<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertArrayEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertArrayEquals($expected, $actual);
    }


    protected function getExpected() : array {
        return ['a', 'b', 'c'];
    }

    public function getGoodActual() : array {
        return [
            [['a', 'b', 'c']]
        ];
    }

    public function getBadActual() : array {
        return [
            [['z', 'x', 'y']],
            [1],
            [[]],
            [true],
            [null],
            [new \stdClass()]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }

}