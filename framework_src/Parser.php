<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\DataProvider;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\Model\AfterAllMethodAware;
use Cspray\Labrador\AsyncUnit\Model\AfterAllMethodModel;
use Cspray\Labrador\AsyncUnit\Model\AfterEachMethodAware;
use Cspray\Labrador\AsyncUnit\Model\AfterEachMethodModel;
use Cspray\Labrador\AsyncUnit\Model\BeforeAllMethodAware;
use Cspray\Labrador\AsyncUnit\Model\BeforeAllMethodModel;
use Cspray\Labrador\AsyncUnit\Model\BeforeEachMethodAware;
use Cspray\Labrador\AsyncUnit\Model\BeforeEachMethodModel;
use Cspray\Labrador\AsyncUnit\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestMethodModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\Attribute\TestSuite as TestSuiteAttribute;
use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite as DefaultTestSuiteAttribute;
use Cspray\Labrador\AsyncUnit\NodeVisitor\AsyncUnitVisitor;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Generator;
use SplFileInfo;
use stdClass;

final class Parser {

    private const REQUIRE_HOOK_IS_STATIC = true;
    private const DO_NOT_REQUIRE_HOOK_IS_STATIC = false;

    use AttributeGroupTraverser;

    private PhpParser $phpParser;
    private NodeTraverser $nodeTraverser;

    public function __construct() {
        $this->phpParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
    }

    public function parse(string|array $dirs) : ParserResult {
        $defaultTestSuite = null;
        $nonDefaultTestSuites = [];
        $plugins = [];
        $dirs = is_string($dirs) ? [$dirs] : $dirs;
        $parseState = new stdClass();
        $parseState->totalTestCaseCount = 0;
        $parseState->totalTestCount = 0;

        foreach ($this->parseDirs($dirs, $parseState) as $model) {
            // The parseDirs implementation is required to yield back models in the order in which we have listed them
            // in this if/elseif statement. ALL TestSuiteModels should be yielded, then all TestCase models and finally
            // any PluginModel. Failure to yield these models in the correct order will result in a fatal error
            if ($model instanceof TestSuiteModel) {
                if ($model->isDefaultTestSuite()) {
                    $defaultTestSuite = $model;
                } else {
                    $nonDefaultTestSuites[$model->getClass()] = $model;
                }
            } else if ($model instanceof TestCaseModel) {
                $parseState->totalTestCaseCount++;
                if (is_null($model->getTestSuiteClass())) {
                    $defaultTestSuite->addTestCaseModel($model);
                } else {
                    if ($defaultTestSuite->getClass() === $model->getTestSuiteClass()) {
                        $defaultTestSuite->addTestCaseModel($model);
                    } else {
                        $nonDefaultTestSuites[$model->getTestSuiteClass()]->addTestCaseModel($model);
                    }
                }
            } else if ($model instanceof PluginModel) {
                $plugins[] = $model;
            }
        }
        $testSuites = array_values($nonDefaultTestSuites);
        if (!empty($defaultTestSuite->getTestCaseModels())) {
            array_unshift($testSuites, $defaultTestSuite);
        }
        return new ParserResult($testSuites, $plugins, $parseState->totalTestCaseCount, $parseState->totalTestCount);
    }

    private function parseDirs(array $dirs, stdClass $state) : Generator {
        $asyncUnitVisitor = $this->doParseDirectories($dirs);
        $classMethods = $asyncUnitVisitor->getAnnotatedClassMethods();

        // We need to make sure there aren't any class methods that could be annotated but not extending
        // the correct TestCase or TestSuite
        $this->validateAnnotatedMethodIsAsyncUnitType($classMethods);

        $testSuiteClasses = $this->filterClassImplementsTestSuite($asyncUnitVisitor->getClasses());
        if (empty($testSuiteClasses)) {
            yield new TestSuiteModel(DefaultTestSuite::class, true);
        } else {
            $hasDefaultTestSuite = false;
            foreach ($testSuiteClasses as $testSuiteClass) {
                $defaultTestSuiteAttribute = $this->findAttribute(DefaultTestSuiteAttribute::class, ...$testSuiteClass->attrGroups);
                if (!$hasDefaultTestSuite && !is_null($defaultTestSuiteAttribute)) {
                    $hasDefaultTestSuite = true;
                }
                $testSuiteModel = new TestSuiteModel($testSuiteClass->namespacedName->toString(), !is_null($defaultTestSuiteAttribute));

                $this->addBeforeAllMethods($testSuiteModel, $classMethods, self::DO_NOT_REQUIRE_HOOK_IS_STATIC);
                $this->addBeforeEachMethods($testSuiteModel, $classMethods);
                $this->addAfterEachMethods($testSuiteModel, $classMethods);
                $this->addAfterAllMethods($testSuiteModel, $classMethods, self::DO_NOT_REQUIRE_HOOK_IS_STATIC);

                yield $testSuiteModel;
            }
            if (!$hasDefaultTestSuite) {
                yield new TestSuiteModel(DefaultTestSuite::class, true);
            }
        }

        $testCaseClasses = $this->filterClassExtendsTestCase($asyncUnitVisitor->getClasses());
        foreach ($testCaseClasses as $testCaseClass) {
            if ($testCaseClass->isAbstract()) {
                continue;
            }

            $testSuiteAttribute = $this->findAttribute(TestSuiteAttribute::class, ...$testCaseClass->attrGroups);
            $testSuiteClassName = null;
            if (!is_null($testSuiteAttribute)) {
                // Right now we are making a huge assumption that the TestSuite is being specified by declaring it as a class constant, i.e. MyTestSuite::class
                $testSuiteClassName = $testSuiteAttribute->args[0]->value->class->toString();
            }

            $testCaseModel = new TestCaseModel($testCaseClass->namespacedName->toString(), $testSuiteClassName);

            $this->addTestsToTestCaseModel($testCaseClasses, $classMethods, $testCaseModel, $testCaseModel->getClass(), $state);
            if (empty($testCaseModel->getTestMethodModels())) {
                $msg = sprintf(
                    'Failure compiling "%s". There were no #[Test] found.',
                    $testCaseModel->getClass()
                );
                throw new TestCompilationException($msg);
            }

            $this->addBeforeAllMethods($testCaseModel, $classMethods, self::REQUIRE_HOOK_IS_STATIC);
            $this->addBeforeEachMethods($testCaseModel, $classMethods);
            $this->addAfterEachMethods($testCaseModel, $classMethods);
            $this->addAfterAllMethods($testCaseModel, $classMethods, self::REQUIRE_HOOK_IS_STATIC);
            yield $testCaseModel;
        }

        $pluginClasses = $this->filterClassImplementsCustomAssertionPlugin($asyncUnitVisitor->getClasses());
        foreach ($pluginClasses as $pluginClass) {
            yield new PluginModel($pluginClass->namespacedName->toString());
        }
    }

    private function doParseDirectories(array $dirs) : AsyncUnitVisitor {
        $nodeConnectingVisitor = new NodeConnectingVisitor();
        $nameResolver = new NameResolver();
        $asyncUnitVisitor = new AsyncUnitVisitor();

        $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
        $this->nodeTraverser->addVisitor($nameResolver);
        $this->nodeTraverser->addVisitor($asyncUnitVisitor);

        foreach ($dirs as $dir) {
            $dirIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::KEY_AS_PATHNAME |
                    FilesystemIterator::CURRENT_AS_FILEINFO |
                    FilesystemIterator::SKIP_DOTS
                )
            );

            /** @var SplFileInfo $file */
            foreach ($dirIterator as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }
                $statements = $this->phpParser->parse(file_get_contents($file->getRealPath()));
                $this->nodeTraverser->traverse($statements);
            }
        }

        return $asyncUnitVisitor;
    }

    /**
     * @param Class_[] $classes
     * @return Class_[]
     */
    private function filterClassImplementsTestSuite(array $classes) : array {
        $testSuites = [];
        foreach ($classes as $class) {
            if ($this->doesClassImplementTestSuite($class)) {
                $testSuites[] = $class;
            }
        }

        return $testSuites;
    }

    /**
     * @param Class_[] $classes
     * @return Class_[]
     */
    private function filterClassExtendsTestCase(array $classes) : array {
        $testCases = [];
        foreach ($classes as $class) {
            if ($this->doesClassExtendTestCase($class)) {
                $testCases[] = $class;
            }
        }
        return $testCases;
    }

    /**
     * @param Class_[] $classes
     * @return Class_[]
     */
    private function filterClassImplementsCustomAssertionPlugin(array $classes) : array {
        $plugins = [];
        foreach ($classes as $class) {
            $classImplements = class_implements($class->namespacedName->toString());
            if (in_array(CustomAssertionPlugin::class, $classImplements)) {
                $plugins[] = $class;
            }

        }
        return $plugins;
    }

    /**
     * @param Class_ $class
     * @return bool
     */
    private function doesClassExtendTestCase(Class_ $class) : bool {
        // This is reliant on the class being autoloadable at time of parsing... need to decide
        // whether or not we want to rely on this or actually parse through the extends chain using
        // static analysis... this method is certainly easier but blurs the line between compilation
        // and runtime a little bit. tl;dr Should compilation step be able to autoload classes?
        return is_subclass_of($class->namespacedName->toString(), TestCase::class);
    }

    private function doesClassImplementTestSuite(Class_ $class) : bool {
        return is_subclass_of($class->namespacedName->toString(), TestSuite::class);
    }

    /**
     * @param BeforeAllMethodAware $model
     * @param ClassMethod[] $classMethods
     */
    private function addBeforeAllMethods(BeforeAllMethodAware $model, array $classMethods, bool $requireMethodIsStatic) : void {
        foreach ($classMethods as $classMethod) {
            if ($model->getClass() !== $classMethod->getAttribute('parent')->namespacedName->toString()) {
                continue;
            }

            if ($this->findAttribute(BeforeAll::class, ...$classMethod->attrGroups)) {
                if ($requireMethodIsStatic && !$classMethod->isStatic()) {
                    $msg = sprintf(
                        'Failure compiling "%s". The non-static method "%s" cannot be used as a #[BeforeAll] hook.',
                        $classMethod->getAttribute('parent')->namespacedName->toString(),
                        $classMethod->name->toString()
                    );
                    throw new TestCompilationException($msg);
                }
                $beforeAllMethod = new BeforeAllMethodModel(
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString()
                );
                $model->addBeforeAllMethod($beforeAllMethod);
            }
        }
    }

    /**
     * @param BeforeEachMethodAware $model
     * @param ClassMethod[] $classMethods
     */
    private function addBeforeEachMethods(BeforeEachMethodAware $model, array $classMethods) : void {
        foreach ($classMethods as $classMethod) {
            if ($model->getClass() !== $classMethod->getAttribute('parent')->namespacedName->toString()) {
                continue;
            }

            if ($this->findAttribute(BeforeEach::class, ...$classMethod->attrGroups)) {
                $beforeEachMethod = new BeforeEachMethodModel(
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString()
                );
                $model->addBeforeEachMethod($beforeEachMethod);
            }
        }
    }

    /**
     * @param AfterEachMethodAware $model
     * @param ClassMethod[] $classMethods
     */
    private function addAfterEachMethods(AfterEachMethodAware $model, array $classMethods) : void {
        foreach ($classMethods as $classMethod) {
            if ($model->getClass() !== $classMethod->getAttribute('parent')->namespacedName->toString()) {
                continue;
            }

            if ($this->findAttribute(AfterEach::class, ...$classMethod->attrGroups)) {
                $afterEachMethod = new AfterEachMethodModel(
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString()
                );
                $model->addAfterEachMethod($afterEachMethod);
            }
        }
    }

    /**
     * @param AfterAllMethodAware $model
     * @param ClassMethod[] $classMethods
     */
    private function addAfterAllMethods(AfterAllMethodAware $model, array $classMethods, bool $requireMethodIsStatic) : void {
        foreach ($classMethods as $classMethod) {
            if ($model->getClass() !== $classMethod->getAttribute('parent')->namespacedName->toString()) {
                continue;
            }

            if ($this->findAttribute(AfterAll::class, ...$classMethod->attrGroups)) {
                if ($requireMethodIsStatic && !$classMethod->isStatic()) {
                    $msg = sprintf(
                        'Failure compiling "%s". The non-static method "%s" cannot be used as a #[AfterAll] hook.',
                        $classMethod->getAttribute('parent')->namespacedName->toString(),
                        $classMethod->name->toString()
                    );
                    throw new TestCompilationException($msg);
                }

                $afterAllMethod = new AfterAllMethodModel(
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString()
                );
                $model->addAfterAllMethod($afterAllMethod);
            }
        }
    }

    private function addTestsToTestCaseModel(array $classes, array $classMethods, TestCaseModel $testCaseModel, string $className, stdClass $parseState) {
        foreach ($classMethods as $classMethod) {
            if (!$this->findAttribute(Test::class, ...$classMethod->attrGroups)) {
                continue;
            }
            if ($classMethod->getAttribute('parent')->namespacedName->toString() === $className) {
                $testMethodModel = new TestMethodModel($testCaseModel->getClass(), $classMethod->name->toString());
                $dataProviderAttribute = $this->findAttribute(DataProvider::class, ...$classMethod->attrGroups);
                if (!is_null($dataProviderAttribute)) {
                    $testMethodModel->setDataProvider($dataProviderAttribute->args[0]->value->value);
                }

                $parseState->totalTestCount++;
                $testCaseModel->addTestMethodModel($testMethodModel);
            }
        }

        $extendedClass = $this->getExtendedClass($classes, $className);
        if (!is_null($extendedClass)) {
            $this->addTestsToTestCaseModel($classes, $classMethods, $testCaseModel, $extendedClass->namespacedName->toString(), $parseState);
        }

    }

    /**
     * @param Class_[] $classes
     * @param string $className
     * @return Class_|null
     */
    private function getExtendedClass(array $classes, string $className) : ?Class_ {
        $foundClass = null;
        foreach ($classes as $class) {
            if ($class->namespacedName->toString() === $className) {
                $foundClass = $class;
                break;
            }
        }
        if (is_null($foundClass->extends)) {
            return null;
        }

        foreach ($classes as $class) {
            if ($foundClass->extends->toString() === $class->namespacedName->toString()) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @param ClassMethod[] $classMethods
     */
    private function validateAnnotatedMethodIsAsyncUnitType(array $classMethods) {
        foreach ($classMethods as $classMethod) {
            if (!$this->doesClassImplementTestSuite($classMethod->getAttribute('parent')) && !$this->doesClassExtendTestCase($classMethod->getAttribute('parent'))) {
                $msg = sprintf(
                    'Failure compiling "%s". The method "%s" is annotated with AsyncUnit attributes but this class does not extend "%s".',
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString(),
                    TestCase::class
                );
                throw new TestCompilationException($msg);
            }
        }
    }

}