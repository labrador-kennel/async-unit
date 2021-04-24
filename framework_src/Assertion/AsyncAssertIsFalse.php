<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;


use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Generator;

class AsyncAssertIsFalse extends AbstractAsyncAssertion {

    protected function getAssertion(mixed $resolvedActual) : Assertion {
        return new AssertIsFalse($resolvedActual);
    }

}