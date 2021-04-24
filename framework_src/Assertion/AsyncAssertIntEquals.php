<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Generator;

class AsyncAssertIntEquals extends AbstractAsyncAssertion {

    public function __construct(private int $expected, Promise|Generator|Coroutine $actual) {
        parent::__construct($actual);
    }

    protected function getAssertion(mixed $actual) : Assertion {
        return new AssertIntEquals($this->expected, $actual);
    }
}