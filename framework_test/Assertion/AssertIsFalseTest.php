<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertIsFalseTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonBoolProvider
     */
    public function testBadTypes($value) {
        $this->runBadTypeAssertions($value);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertIsFalse($actual);
    }

    protected function getGoodValue() {
        return false;
    }

    protected function getBadValue() {
        return true;
    }

    protected function getExpectedType() {
        return 'boolean';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return Assertion\AssertionMessage\FalseUnaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\FalseUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\FalseUnaryOperandDetails::class;
    }
}