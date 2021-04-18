<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AsyncAssertIntEquals extends AbstractAsyncAssertion {

    public function __construct(private int $expected) {}

    protected function getAssertion() : Assertion {
        return new AssertIntEquals($this->expected);
    }
}