<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

class DefaultTestSuite implements TestSuite {

    public function getName() : string {
        return 'Default TestSuite';
    }

    public function set(string $key, mixed $value) : void {
        // TODO: Implement set() method.
    }

    public function get(string $key) : mixed {
        // TODO: Implement get() method.
    }
}