<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;

class AsyncAssertArrayEquals extends AbstractAsyncAssertion implements AsyncAssertion {

    public function __construct(private array $expected) {}

    protected function getAssertion() : Assertion {
        return new AssertArrayEquals($this->expected);
    }
}