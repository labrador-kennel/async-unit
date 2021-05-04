<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Model;

use PhpParser\Node\Stmt\ClassMethod;

final class HookModel {

    use MethodModelTrait {
        MethodModelTrait::__construct as setClassAndMethod;
    }

    private string $type;

    public function __construct(ClassMethod $classMethod, string $type) {
        $this->setClassAndMethod(
            $classMethod->getAttribute('parent')->namespacedName->toString(),
            $classMethod->name->toString()
        );
        $this->type = $type;
    }

    public function getType() : string {
        return $this->type;
    }

}