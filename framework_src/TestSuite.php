<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

abstract class TestSuite {

    final public function getName() : string {
        return static::class;
    }

}