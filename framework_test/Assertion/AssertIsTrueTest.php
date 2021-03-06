<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertIsTrueTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIsTrue($actual);
    }

    public function getGoodActual() : array {
        return [
            [true]
        ];
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getBadActual() : array {
        return [
            [false],
            [1],
            [0],
            [[1]],
            [new \stdClass()],
            ['this is not true']
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\TrueUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\TrueUnaryOperandDetails::class;
    }
}