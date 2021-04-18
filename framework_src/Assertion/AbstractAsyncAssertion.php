<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;
use function Amp\call;

abstract class AbstractAsyncAssertion implements AsyncAssertion {

    final public function assert(Promise|Coroutine|Generator $actual, string $errorMessage = null) : Promise {
        return call(function() use($actual, $errorMessage) {
            $actual = yield call(fn() => $actual);
            $assertion = $this->getAssertion();
            return $assertion->assert($actual, $errorMessage);
        });
    }

    abstract protected function getAssertion() : Assertion;
}