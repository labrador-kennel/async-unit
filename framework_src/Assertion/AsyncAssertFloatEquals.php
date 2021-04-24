<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Generator;

class AsyncAssertFloatEquals extends AbstractAsyncAssertion {

    public function __construct(private float $expected, Promise|Generator|Coroutine $actual) {
        parent::__construct($actual);
    }

    protected function getAssertion(mixed $actual) : Assertion {
        return new AssertFloatEquals($this->expected, $actual);
    }
}