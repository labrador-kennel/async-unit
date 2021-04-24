<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal\NodeVisitor;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Internal\AttributeGroupTraverser;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class TestCaseVisitor extends NodeVisitorAbstract implements NodeVisitor {

    use AttributeGroupTraverser;

    private array $classes = [];
    private array $classMethods = [];

    /**
     * @return Node\Stmt\Class_[]
     */
    public function getClasses() : array {
        return $this->classes;
    }

    /**
     * @return Node\Stmt\ClassMethod[]
     */
    public function getAnnotatedClassMethods() : array {
        return $this->classMethods;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Node\Stmt\Class_) {
            $this->classes[] = $node;
        } else if ($node instanceof Node\Stmt\ClassMethod) {
            if ($this->hasAnyAsyncUnitAttribute($node)) {
                $this->classMethods[] = $node;
            }
        }
    }

    private function hasAnyAsyncUnitAttribute(Node\Stmt\ClassMethod $classMethod) : bool {
        $validAttributes = [
            Test::class,
            BeforeAll::class,
            BeforeEach::class,
            AfterAll::class,
            AfterEach::class
        ];
        foreach ($validAttributes as $validAttribute) {
            if (!is_null($this->findAttribute($validAttribute, ...$classMethod->attrGroups))) {
                return true;
            }
        }
        return false;
    }
}