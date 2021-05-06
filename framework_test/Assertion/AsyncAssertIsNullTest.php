<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\FalseAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\NullAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\NullUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\NullUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertIsNullTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonNullProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected, Promise|Generator|Coroutine $actual) : AsyncAssertion {
        return new AsyncAssertIsNull($actual);
    }

    protected function getExpectedValue() : mixed {
        return null;
    }

    protected function getBadValue() : string {
        return 'non null';
    }

    protected function getExpectedType() : string {
        return 'NULL';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return NullUnaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return NullUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return NullUnaryOperandDetails::class;
    }
}