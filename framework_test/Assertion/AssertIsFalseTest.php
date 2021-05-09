<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertIsFalseTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIsFalse($actual);
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getGoodActual() : array {
        return [
            [false]
        ];
    }

    public function getBadActual() : array {
        return [
            [true],
            [1],
            [0],
            [[]],
            ['not false'],
            [''],
            [new \stdClass()]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\FalseUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\FalseUnaryOperandDetails::class;
    }
}