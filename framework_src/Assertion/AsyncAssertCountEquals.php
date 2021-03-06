<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Generator;

final class AsyncAssertCountEquals extends AbstractAsyncAssertion {

    public function __construct(private int $expected, Promise|Generator|Coroutine $actual) {
        parent::__construct($actual);
    }

    protected function getAssertion(mixed $resolvedActual) : Assertion {
        return new AssertCountEquals($this->expected, $resolvedActual);
    }
}