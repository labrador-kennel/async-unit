<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\DefaultExplicitTestSuite;

use Cspray\Labrador\AsyncUnit\TestSuite;

class MyTestSuite implements TestSuite {

    public function getName() : string {
        return self::class;
    }

    public function set(string $key, mixed $value) : void {
        // TODO: Implement set() method.
    }

    public function get(string $key) : mixed {
        // TODO: Implement get() method.
    }
}