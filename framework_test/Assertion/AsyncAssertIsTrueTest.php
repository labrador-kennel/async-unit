<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\TrueAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertIsTrueTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonBoolProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected, Promise|Generator|Coroutine $actual) : AsyncAssertion {
        return new AsyncAssertIsTrue($actual);
    }

    protected function getExpectedValue() : bool {
        return true;
    }

    protected function getBadValue() : bool {
        return false;
    }

    protected function getExpectedType() : string {
        return 'boolean';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new TrueAssertionComparisonDisplay($actual);
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is true.', $actualType);
    }

    protected function getAssertionString($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}