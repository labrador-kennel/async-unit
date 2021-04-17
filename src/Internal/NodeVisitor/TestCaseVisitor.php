<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Internal\NodeVisitor;

use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;
use Cspray\Labrador\AsyncTesting\Attribute\AfterEach;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeEach;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\Exception\TestCompilationException;
use Cspray\Labrador\AsyncTesting\Internal\Model\AfterAllMethodModel;
use Cspray\Labrador\AsyncTesting\Internal\Model\AfterEachMethodModel;
use Cspray\Labrador\AsyncTesting\Internal\Model\BeforeAllMethodModel;
use Cspray\Labrador\AsyncTesting\Internal\Model\BeforeEachMethodModel;
use Cspray\Labrador\AsyncTesting\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncTesting\Internal\Model\TestMethodModel;
use Cspray\Labrador\AsyncTesting\TestCase;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class TestCaseVisitor extends NodeVisitorAbstract implements NodeVisitor {

    private array $testCaseModels = [];

    /**
     * @return TestCaseModel[]
     */
    public function getTestCaseModels() : array {
        return array_values($this->testCaseModels);
    }

    public function enterNode(Node $node) {
        if ($node instanceof Node\Stmt\Class_) {
            if ($this->isClassTestCase($node)) {
                $this->fetchTestCaseModel($node);
                // we don't do anything here but we still want to get it added to our test case models
                // if you implement this interface we expect you to have some kind of test and we need
                // to check for that
            }
        } else if ($node instanceof Node\Stmt\ClassMethod) {
            if ($this->findAttribute(Test::class, ...$node->attrGroups)) {
                $testCaseName = $node->getAttribute('parent')->namespacedName->toString();
                // we're checking this again so that when our method models call this we're checking for the other
                // case where the #[Test] method may not implement the correct interface
                if (!$this->isClassTestCase($node->getAttribute('parent'))) {
                    $msg = sprintf(
                        'Failure compiling "%s". The method "%s" is marked as #[Test] but this class does not extend "%s".',
                        $testCaseName,
                        $node->name->toString(),
                        TestCase::class
                    );
                    throw new TestCompilationException($msg);
                }
                $methodModel = new TestMethodModel($testCaseName, $node->name->toString());
                $testCaseModel = $this->fetchTestCaseModel($node->getAttribute('parent'));
                $testCaseModel->addTestMethodModel($methodModel);
            } else if ($this->findAttribute(BeforeAll::class, ...$node->attrGroups)) {
                $testCaseName = $node->getAttribute('parent')->namespacedName->toString();
                if (!$this->isClassTestCase($node->getAttribute('parent'))) {
                    $msg = sprintf(
                        'Failure compiling "%s". The method "%s" is marked as #[BeforeAll] but this class does not extend "%s".',
                        $testCaseName,
                        $node->name->toString(),
                        TestCase::class
                    );
                    throw new TestCompilationException($msg);
                }
                if (!$node->isStatic()) {
                    $msg = sprintf(
                        'Failure compiling "%s". The non-static method "%s" cannot be used as a #[BeforeAll] hook.',
                        $testCaseName,
                        $node->name->toString()
                    );
                    throw new TestCompilationException($msg);
                }
                $methodModel = new BeforeAllMethodModel($testCaseName, $node->name->toString());
                $testCaseModel = $this->fetchTestCaseModel($node->getAttribute('parent'));
                $testCaseModel->addBeforeAllMethodModel($methodModel);
            } else if ($this->findAttribute(BeforeEach::class, ...$node->attrGroups)) {
                $testCaseName = $node->getAttribute('parent')->namespacedName->toString();
                if (!$this->isClassTestCase($node->getAttribute('parent'))) {
                    $msg = sprintf(
                        'Failure compiling "%s". The method "%s" is marked as #[BeforeEach] but this class does not extend "%s".',
                        $testCaseName,
                        $node->name->toString(),
                        TestCase::class
                    );
                    throw new TestCompilationException($msg);
                }
                $methodModel = new BeforeEachMethodModel($testCaseName, $node->name->toString());
                $testCaseModel = $this->fetchTestCaseModel($node->getAttribute('parent'));
                $testCaseModel->addBeforeEachMethodModel($methodModel);
            } else if ($this->findAttribute(AfterAll::class, ...$node->attrGroups)) {
                $testCaseName = $node->getAttribute('parent')->namespacedName->toString();
                if (!$this->isClassTestCase($node->getAttribute('parent'))) {
                    $msg = sprintf(
                        'Failure compiling "%s". The method "%s" is marked as #[AfterAll] but this class does not extend "%s".',
                        $testCaseName,
                        $node->name->toString(),
                        TestCase::class
                    );
                    throw new TestCompilationException($msg);
                }
                if (!$node->isStatic()) {
                    $msg = sprintf(
                        'Failure compiling "%s". The non-static method "%s" cannot be used as a #[AfterAll] hook.',
                        $testCaseName,
                        $node->name->toString()
                    );
                    throw new TestCompilationException($msg);
                }
                $methodModel = new AfterAllMethodModel($testCaseName, $node->name->toString());
                $testCaseModel = $this->fetchTestCaseModel($node->getAttribute('parent'));
                $testCaseModel->addAfterAllMethodModel($methodModel);
            } else if ($this->findAttribute(AfterEach::class, ...$node->attrGroups)) {
                $testCaseName = $node->getAttribute('parent')->namespacedName->toString();
                if (!$this->isClassTestCase($node->getAttribute('parent'))) {
                    $msg = sprintf(
                        'Failure compiling "%s". The method "%s" is marked as #[AfterEach] but this class does not extend "%s".',
                        $testCaseName,
                        $node->name->toString(),
                        TestCase::class
                    );
                    throw new TestCompilationException($msg);
                }
                $methodModel = new AfterEachMethodModel($testCaseName, $node->name->toString());
                $testCaseModel = $this->fetchTestCaseModel($node->getAttribute('parent'));
                $testCaseModel->addAfterEachMethodModel($methodModel);
            }
        }
    }

    private function isTestCaseModelCached(string $testCaseClass) : bool {
        return isset($this->testCaseModels[$testCaseClass]);
    }

    private function fetchTestCaseModel(Node\Stmt\Class_ $node) : TestCaseModel {
        $testCaseName = $node->namespacedName->toString();
        if (!$this->isTestCaseModelCached($testCaseName)) {
            $this->testCaseModels[$testCaseName] = new TestCaseModel($testCaseName);
        }

        return $this->testCaseModels[$testCaseName];
    }

    private function isClassTestCase(Node\Stmt\Class_ $class) : bool {
        return !is_null($class->extends) && $class->extends->toString() === TestCase::class;
    }


    private function findAttribute(string $attributeType, AttributeGroup... $attributeGroups) : ?Attribute {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() === $attributeType) {
                    return $attribute;
                }
            }
        }

        return null;
    }


}