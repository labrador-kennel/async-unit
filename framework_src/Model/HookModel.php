<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Model;

use Cspray\Labrador\AsyncUnit\HookType;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * A representation of an invokable class method annotated with an AsyncUnit hook attribute.
 *
 * @package Cspray\Labrador\AsyncUnit\Model
 */
final class HookModel {

    use MethodModelTrait {
        MethodModelTrait::__construct as setClassAndMethod;
    }

    private HookType $type;

    public function __construct(string $class, string $classMethod, HookType $type) {
        $this->setClassAndMethod($class, $classMethod);
        $this->type = $type;
    }

    /**
     * Returns the type of the hook; this corresponds to the simple class name for the attribute annotated on the class
     * method.
     *
     * @return HookType
     * @todo In 8.1 convert this to use a native PHP enum
     */
    public function getType() : HookType {
        return $this->type;
    }

}