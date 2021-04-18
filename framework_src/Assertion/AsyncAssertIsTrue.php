<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AsyncAssertIsTrue extends AbstractAsyncAssertion {

    protected function getAssertion() : Assertion {
        return new AssertIsTrue();
    }
}