<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

use Amp\Coroutine;
use Amp\Promise;
use Generator;

final class AsyncAssertionContext {

    private int $count = 0;

    private function __construct() {}

    public function getAssertionCount() : int {
        return $this->count;
    }

    /**
     * Compare that an $actual string resolved from a promisor is equal to $expected.
     *
     * @param string $expected
     * @param Promise<string>|Generator<string>|Coroutine<string> $actual
     * @param string|null $message
     */
    public function stringEquals(string $expected, Promise|Generator|Coroutine $actual, string $message = null) : Promise {

    }

}