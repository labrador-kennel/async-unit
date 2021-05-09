<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertIsNullTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIsNull($actual);
    }

    public function getGoodActual() : array {
        return [
            [null]
        ];
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getBadActual() : array {
        return [
            ['not null'],
            [1],
            [false],
            [0],
            [[]]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\NullUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\NullUnaryOperandDetails::class;
    }
}