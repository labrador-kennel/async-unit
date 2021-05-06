<?php

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use PHPUnit\Framework\TestCase;

class AssertStringEqualsTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonStringProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertStringEquals($value, $actual);
    }

    protected function getGoodValue() {
        return 'async unit';
    }

    protected function getBadValue() {
        return 'blocking code';
    }

    protected function getExpectedType() {
        return 'string';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return Assertion\AssertionMessage\InvalidTypeBinaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandDetails::class;
    }

}
