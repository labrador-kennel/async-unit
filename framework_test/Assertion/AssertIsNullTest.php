<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertIsNullTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonNullProvider
     */
    public function testBadTypes($value) {
        $this->runBadTypeAssertions($value);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertIsNull($actual);
    }

    protected function getGoodValue() {
        return null;
    }

    protected function getBadValue() {
        return 'not null';
    }

    protected function getExpectedType() {
        return 'NULL';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return Assertion\AssertionMessage\NullUnaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\NullUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\NullUnaryOperandDetails::class;
    }
}