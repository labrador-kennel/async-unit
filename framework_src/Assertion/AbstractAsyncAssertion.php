<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;
use function Amp\call;

abstract class AbstractAsyncAssertion implements AsyncAssertion {

    public function __construct(private Promise|Generator|Coroutine $actual) {}

    final public function assert() : Promise {
        return call(function() {
            $actual = yield call(fn() => $this->actual);
            $assertion = $this->getAssertion($actual);
            return $assertion->assert();
        });
    }

    abstract protected function getAssertion(mixed $resolvedActual) : Assertion;
}