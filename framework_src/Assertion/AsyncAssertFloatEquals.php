<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AsyncAssertFloatEquals extends AbstractAsyncAssertion {

    public function __construct(private float $expected) {}

    protected function getAssertion() : Assertion {
        return new AssertFloatEquals($this->expected);
    }
}