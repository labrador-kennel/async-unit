<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Internal\Model;

trait MethodModelTrait {

    public function __construct(
        private String $class,
        private String $method
    ) {}

    public function getClass() : string {
        return $this->class;
    }

    public function getMethod() : string {
        return $this->method;
    }

}