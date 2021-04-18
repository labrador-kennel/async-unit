<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertStringEquals;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Internal\LastAssertionCalledTrait;
use Generator;
use function Amp\call;

final class AsyncAssertionContext {

    use LastAssertionCalledTrait;

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
        return call(function() use($expected, $actual, $message) {
            $results = yield (new AsyncAssertStringEquals($expected))->assert($actual, $message);
            $this->count++;
            if (!$results->isSuccessful()) {
                throw new AssertionFailedException(
                    $results->getErrorMessage(),
                    $results->getComparisonDisplay(),
                    $this->getLastAssertionFile(),
                    $this->getLastAssertionLine()
                );
            }
        });
    }

}