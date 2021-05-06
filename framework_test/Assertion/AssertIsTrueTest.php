<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertIsTrueTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonBoolProvider
     */
    public function testBadTypes($value) {
        $this->runBadTypeAssertions($value);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertIsTrue($actual);
    }

    protected function getGoodValue() {
        return true;
    }

    protected function getBadValue() {
        return false;
    }

    protected function getExpectedType() {
        return 'boolean';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return Assertion\AssertionMessage\TrueUnaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\TrueUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\TrueUnaryOperandDetails::class;
    }
}