<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\NodeVisitor;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\AttributeGroupTraverser;
use Cspray\Labrador\AsyncUnit\CustomAssertionPlugin;
use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;
use PhpParser\Builder\Class_;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class AsyncUnitVisitor extends NodeVisitorAbstract implements NodeVisitor {

    use AttributeGroupTraverser;

    private array $testSuites = [];
    private array $testCases = [];
    private array $plugins = [];
    private array $classMethods = [];

    /**
     * @return Node\Stmt\Class_[]
     */
    public function getTestSuites() : array {
        return $this->testSuites;
    }

    /**
     * @return Node\Stmt\Class_[]
     */
    public function getTestCases() : array {
        return $this->testCases;
    }

    /**
     * @return Node\Stmt\Class_[]
     */
    public function getPlugins() : array {
        return $this->plugins;
    }

    /**
     * @return Node\Stmt\ClassMethod[]
     */
    public function getAnnotatedClassMethods() : array {
        return $this->classMethods;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Node\Stmt\Class_) {
            if (is_subclass_of($node->namespacedName->toString(), TestCase::class)) {
                $this->testCases[] = $node;
            } else if (is_subclass_of($node->namespacedName->toString(), TestSuite::class)) {
                $this->testSuites[] = $node;
            } else if (is_subclass_of($node->namespacedName->toString(), CustomAssertionPlugin::class)) {
                $this->plugins[] = $node;
            }
        } else if ($node instanceof Node\Stmt\ClassMethod) {
            if ($this->hasAnyAsyncUnitAttribute($node)) {
                $this->classMethods[] = $node;
            }
        }
    }

    private function hasAnyAsyncUnitAttribute(Node\Stmt\ClassMethod $classMethod) : bool {
        $validAttributes = [
            Test::class => fn() => $this->validateTest($classMethod),
            BeforeAll::class => fn() => $this->validateBeforeAll($classMethod),
            BeforeEach::class => fn() => $this->validateBeforeEach($classMethod),
            AfterAll::class => fn() => $this->validateAfterAll($classMethod),
            AfterEach::class => fn() => $this->validateAfterEach($classMethod),
            BeforeEachTest::class => fn() => true,
            AfterEachTest::class => fn() => true
        ];
        foreach ($validAttributes as $validAttribute => $validator) {
            if (!is_null($this->findAttribute($validAttribute, ...$classMethod->attrGroups))) {
                $validator();
                return true;
            }
        }
        return false;
    }

    private function validateTest(Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[Test] but this class does not extend "%s".',
                $className,
                $classMethod->name->toString(),
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }
    }

    private function validateBeforeEach(Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[BeforeEach] but this class does not extend "%s" or "%s".',
                $className,
                $classMethod->name->toString(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }
    }

    private function validateAfterEach(Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[AfterEach] but this class does not extend "%s" or "%s".',
                $className,
                $classMethod->name->toString(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }
    }

    private function validateBeforeAll(Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[BeforeAll] but this class does not extend "%s" or "%s".',
                $className,
                $classMethod->name->toString(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        } else if (is_subclass_of($className, TestCase::class)) {
            if (!$classMethod->isStatic()) {
                $msg = sprintf(
                    'Failure compiling "%s". The non-static method "%s" cannot be used as a #[BeforeAll] hook.',
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString(),
                );
                throw new TestCompilationException($msg);
            }
        }
    }

    private function validateAfterAll(Node\Stmt\ClassMethod $classMethod) {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[AfterAll] but this class does not extend "%s" or "%s".',
                $className,
                $classMethod->name->toString(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        } else if (is_subclass_of($className, TestCase::class)) {
            if (!$classMethod->isStatic()) {
                $msg = sprintf(
                    'Failure compiling "%s". The non-static method "%s" cannot be used as a #[AfterAll] hook.',
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString(),
                );
                throw new TestCompilationException($msg);
            }
        }
    }
}