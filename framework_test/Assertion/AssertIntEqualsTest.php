<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertIntEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIntEquals($expected, $actual);
    }

    protected function getExpected() : int {
        return 1234;
    }

    public function getGoodActual() : array {
        return [
            [1234]
        ];
    }

    public function getBadActual() : array {
        return [
            [9876],
            [1234.56],
            [[]],
            ['not an int'],
            [new \stdClass()],
            [null],
            [true]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }
}