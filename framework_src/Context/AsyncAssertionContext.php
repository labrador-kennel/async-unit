<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertArrayEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertFloatEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIntEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsFalse;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsNull;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsTrue;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertStringEquals;
use Cspray\Labrador\AsyncUnit\LastAssertionCalledTrait;
use Generator;
use function Amp\call;

final class AsyncAssertionContext {

    use LastAssertionCalledTrait;
    use SharedAssertionContextTrait;

    private function __construct(private CustomAssertionContext $customAssertionContext) {}

    public function arrayEquals(array $expected, Promise|Generator|Coroutine $actual, string $message = null) : Promise {
        return call(function() use($expected, $actual, $message) {
            $isNot = $this->isNot;
            $this->invokedAssertionContext();

            $results = yield (new AsyncAssertArrayEquals($expected, $actual))->assert();

            $this->handleAssertionResults($results, $isNot, $message);
        });
    }

    public function floatEquals(float $expected, Promise|Generator|Coroutine $actual, string $message = null) : Promise {
        return call(function() use($expected, $actual, $message) {
            $isNot = $this->isNot;
            $this->invokedAssertionContext();

            $results = yield (new AsyncAssertFloatEquals($expected, $actual))->assert();

            $this->handleAssertionResults($results, $isNot, $message);
        });
    }

    public function intEquals(int $expected, Promise|Generator|Coroutine $actual, string $message = null) : Promise {
        return call(function() use($expected, $actual, $message) {
            $isNot = $this->isNot;
            $this->invokedAssertionContext();

            $results = yield (new AsyncAssertIntEquals($expected, $actual))->assert();

            $this->handleAssertionResults($results, $isNot, $message);
        });
    }

    /**
     * Compare that an $actual string resolved from a promisor is equal to $expected.
     *
     * @param string $expected
     * @param Promise<string>|Generator<string>|Coroutine<string> $actual
     * @param string|null $message
     * @return Promise
     */
    public function stringEquals(string $expected, Promise|Generator|Coroutine $actual, string $message = null) : Promise {
        return call(function() use($expected, $actual, $message) {
            $isNot = $this->isNot;
            $this->invokedAssertionContext();
            $results = yield (new AsyncAssertStringEquals($expected, $actual))->assert();
            $this->handleAssertionResults($results, $isNot, $message);
        });
    }

    public function isTrue(Promise|Generator|Coroutine $actual, string $message = null) : Promise {
        return call(function() use($actual, $message) {
            $isNot = $this->isNot;
            $this->invokedAssertionContext();
            $results = yield (new AsyncAssertIsTrue($actual))->assert();
            $this->handleAssertionResults($results, $isNot, $message);
        });
    }

    public function isFalse(Promise|Generator|Coroutine $actual, string $message = null) : Promise {
        return call(function() use($actual, $message) {
            $isNot = $this->isNot;
            $this->invokedAssertionContext();
            $results = yield (new AsyncAssertIsFalse($actual))->assert();
            $this->handleAssertionResults($results, $isNot, $message);
        });
    }

    public function isNull(Promise|Generator|Coroutine $actual, string $message = null) : Promise {
        return call(function () use($actual, $message) {
            $isNot = $this->isNot;
            $this->invokedAssertionContext();
            $results = yield (new AsyncAssertIsNull($actual))->assert();
            $this->handleAssertionResults($results, $isNot, $message);
        });
    }

    public function __call(string $methodName, array $args) : Promise {
        return call(function() use($methodName, $args) {
            $isNot = $this->isNot;
            $this->invokedAssertionContext();
            $results = yield $this->customAssertionContext->createAsyncAssertion($methodName, ...$args)->assert();
            $this->handleAssertionResults($results, $isNot, null);
        });
    }
}