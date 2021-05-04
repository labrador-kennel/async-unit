<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\BinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\InvalidTypeBinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\InvalidTypeBinaryOperandSummaryTest;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertIntEqualsTest extends AbstractAsyncAssertionTestCase {
    /**
     * @dataProvider nonIntProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected, Promise|Generator|Coroutine $actual) : AsyncAssertion {
        return new AsyncAssertIntEquals($expected, $actual);
    }

    protected function getExpectedValue() : int {
        return 1;
    }

    protected function getBadValue() : int {
        return 2;
    }

    protected function getExpectedType() : string {
        return 'integer';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return InvalidTypeBinaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }
}