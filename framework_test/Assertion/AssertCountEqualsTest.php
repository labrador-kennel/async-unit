<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Stub\CountableStub;

class AssertCountEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertCountEquals($expected, $actual);
    }

    protected function getExpected() : int {
        return 5;
    }

    public function getGoodActual() : array {
        return [
            [[1, 2, 3, 4, 5]],
            [['a', 'b', 'c', 'd', 'e']],
            [new CountableStub(5)]
        ];
    }

    public function getBadActual() : array {
        return [
            [[]],
            [[1, 2, 3, 4]],
            [[1, 2, 3, 4, 5, 6]],
            [new CountableStub(4)]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\CountEqualsMessage::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\CountEqualsMessage::class;
    }
}