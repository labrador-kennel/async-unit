<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertFloatEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertFloatEquals($expected, $actual);
    }

    protected function getExpected() : float {
        return 9876.54;
    }

    public function getGoodActual() : array {
        return [
            [9876.54]
        ];
    }

    public function getBadActual() : array {
        return [
            [1234.56],
            [9876],
            [false],
            [null],
            [[]],
            ['this is not a float'],
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