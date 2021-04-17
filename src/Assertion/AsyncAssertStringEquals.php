<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncTesting\AsyncAssertion;
use Generator;
use function Amp\call;

class AsyncAssertStringEquals implements AsyncAssertion {

    public function __construct(private string $expected) {}

    public function assert(Promise|Coroutine|Generator $actual, string $errorMessage = null) : Promise {
        return call(function() use($actual, $errorMessage) {
            $actual = yield call(fn() => $actual);
            $assertion = new AssertStringEquals($this->expected);
            return $assertion->assert($actual, $errorMessage);
        });
    }

}