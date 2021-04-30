<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface TestSuite {

    public function getName() : string;

    public function set(string $key, mixed $value) : void;

    public function get(string $key) : mixed;

}