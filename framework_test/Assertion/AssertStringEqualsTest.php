<?php

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;

class AssertStringEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertStringEquals($value, $actual);
    }

    public function getGoodActual() : array {
        return [
            ['async unit']
        ];
    }

    protected function getExpected() : string {
        return 'async unit';
    }

    public function getBadActual() : array {
        return [
            ['blocking code'],
            ['phpunit'],
            [1],
            [1.23],
            [null],
            [true],
            [['async unit']],
            [new \stdClass()]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandDetails::class;
    }

}
