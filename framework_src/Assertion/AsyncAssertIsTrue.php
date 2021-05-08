<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

final class AsyncAssertIsTrue extends AbstractAsyncAssertion {

    protected function getAssertion(mixed $resolvedActual) : Assertion {
        return new AssertIsTrue($resolvedActual);
    }
}