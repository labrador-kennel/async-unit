<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertFloatEqualsTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonFloatProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertFloatEquals($value, $actual);
    }

    protected function getGoodValue() {
        return 9876.54;
    }

    protected function getBadValue() {
        return 1234.56;
    }

    protected function getExpectedType() {
        return 'double';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return Assertion\AssertionMessage\InvalidTypeBinaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }
}