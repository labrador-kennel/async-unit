<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\Internal\Model\AfterAllMethodModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\AfterEachMethodModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\BeforeAllMethodModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\BeforeEachMethodModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestMethodModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\Internal\NodeVisitor\TestCaseVisitor;
use Cspray\Labrador\AsyncUnit\TestCase;
use FilesystemIterator;
use Generator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal
 */
class Parser {

    use AttributeGroupTraverser;

    private PhpParser $phpParser;
    private NodeTraverser $nodeTraverser;

    public function __construct() {
        $this->phpParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
    }

    /**
     * @param string|array $dirs
     * @return TestSuiteModel[]
     */
    public function parse(string|array $dirs) : array {
        $testSuiteModel = new TestSuiteModel();
        $dirs = is_string($dirs) ? [$dirs] : $dirs;
        foreach ($this->parseDirs($dirs) as $model) {
            $testSuiteModel->addTestCaseModel($model);
        }

        return [$testSuiteModel];
    }

    private function parseDirs(array $dirs) : Generator {
        foreach ($dirs as $dir) {
            $dirIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::KEY_AS_PATHNAME |
                    FilesystemIterator::CURRENT_AS_FILEINFO |
                    FilesystemIterator::SKIP_DOTS
                )
            );

            /** @var \SplFileInfo $file */
            foreach ($dirIterator as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $statements = $this->phpParser->parse(file_get_contents($file->getRealPath()));
                $nodeConnectingVisitor = new NodeConnectingVisitor();
                $nameResolver = new NameResolver();
                $testCaseVisitor = new TestCaseVisitor();

                $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
                $this->nodeTraverser->addVisitor($nameResolver);
                $this->nodeTraverser->addVisitor($testCaseVisitor);
                $this->nodeTraverser->traverse($statements);

                $testCaseClasses = $this->filterClassExtendsTestCase($testCaseVisitor->getClasses());
                $classMethods = $testCaseVisitor->getAnnotatedClassMethods();

                $this->validateAnnotatedMethodsExtendsTestCase($classMethods);

                foreach ($testCaseClasses as $testCaseClass) {
                    $testCaseModel = new TestCaseModel($testCaseClass->namespacedName->toString());
                    foreach ($classMethods as $classMethod) {
                        if ($classMethod->getAttribute('parent')->namespacedName->toString() !== $testCaseClass->namespacedName->toString()) {
                            continue;
                        }
                        if ($this->findAttribute(Test::class, ...$classMethod->attrGroups)) {
                            $testMethod = new TestMethodModel($testCaseClass->namespacedName->toString(), $classMethod->name->toString());
                            $testCaseModel->addTestMethodModel($testMethod);
                        } else if ($this->findAttribute(BeforeAll::class, ...$classMethod->attrGroups)) {
                            if (!$classMethod->isStatic()) {
                                $msg = sprintf(
                                    'Failure compiling "%s". The non-static method "%s" cannot be used as a #[BeforeAll] hook.',
                                    $testCaseClass->namespacedName->toString(),
                                    $classMethod->name->toString()
                                );
                                throw new TestCompilationException($msg);
                            }
                            $beforeAllMethod = new BeforeAllMethodModel($testCaseClass->namespacedName->toString(), $classMethod->name->toString());
                            $testCaseModel->addBeforeAllMethodModel($beforeAllMethod);
                        } else if ($this->findAttribute(BeforeEach::class, ...$classMethod->attrGroups)) {
                            $beforeEachMethod = new BeforeEachMethodModel($testCaseClass->namespacedName->toString(), $classMethod->name->toString());
                            $testCaseModel->addBeforeEachMethodModel($beforeEachMethod);
                        } else if ($this->findAttribute(AfterEach::class, ...$classMethod->attrGroups)) {
                            $afterEachMethod = new AfterEachMethodModel($testCaseClass->namespacedName->toString(), $classMethod->name->toString());
                            $testCaseModel->addAfterEachMethodModel($afterEachMethod);
                        } else if ($this->findAttribute(AfterAll::class, ...$classMethod->attrGroups)) {
                            if (!$classMethod->isStatic()) {
                                $msg = sprintf(
                                    'Failure compiling "%s". The non-static method "%s" cannot be used as a #[AfterAll] hook.',
                                    $testCaseClass->namespacedName->toString(),
                                    $classMethod->name->toString()
                                );
                                throw new TestCompilationException($msg);
                            }
                            $afterAllMethod = new AfterAllMethodModel($testCaseClass->namespacedName->toString(), $classMethod->name->toString());
                            $testCaseModel->addAfterAllMethodModel($afterAllMethod);
                        }
                    }

                    if (empty($testCaseModel->getTestMethodModels())) {
                        $msg = sprintf(
                            'Failure compiling "%s". There were no #[Test] found.',
                            $testCaseModel->getTestCaseClass()
                        );
                        throw new TestCompilationException($msg);
                    }


                    yield $testCaseModel;
                }
            }
        }
    }

    /**
     * @param Class_[] $classes
     * @return Class_[]
     */
    private function filterClassExtendsTestCase(array $classes) : array {
        $testCases = [];
        foreach ($classes as $class) {
            if (!is_null($class->extends) && $class->extends->toString() === TestCase::class) {
                $testCases[] = $class;
            }
        }
        return $testCases;
    }

    /**
     * @param ClassMethod[] $classMethods
     */
    private function validateAnnotatedMethodsExtendsTestCase(array $classMethods) {
        foreach ($classMethods as $classMethod) {
            if (is_null($classMethod->getAttribute('parent')->extends) || $classMethod->getAttribute('parent')->extends->toString() !== TestCase::class) {
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