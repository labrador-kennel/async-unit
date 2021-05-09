<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertIsEmptyTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIsEmpty($actual);
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getGoodActual() : array {
        return [
            [[]],
            [0],
            [null],
            [false],
            ['']
        ];
    }

    public function getBadActual() : array {
        return [
            [[1, 2, 3, 4]],
            ['a']
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\EmptyUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\EmptyUnaryOperandDetails::class;
    }
}