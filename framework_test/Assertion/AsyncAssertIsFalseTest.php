<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\FalseAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\FalseUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\FalseUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertIsFalseTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonBoolProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected, Promise|Generator|Coroutine $actual) : AsyncAssertion {
        return new AsyncAssertIsFalse($actual);
    }

    protected function getExpectedValue() : bool {
        return false;
    }

    protected function getBadValue() : bool {
        return true;
    }

    protected function getExpectedType() : string {
        return 'boolean';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return FalseUnaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return FalseUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return FalseUnaryOperandDetails::class;
    }
}