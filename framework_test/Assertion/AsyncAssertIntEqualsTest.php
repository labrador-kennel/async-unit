<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\BinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertIntEqualsTest extends AbstractAsyncAssertionTestCase {

    protected function getAssertion($expected, Promise|Generator|Coroutine $actual) : AsyncAssertion {
        return new AsyncAssertIntEquals($expected, $actual);
    }

    protected function getExpected() : int {
        return 1;
    }

    public function getGoodActual() : array {
        return [
            [1]
        ];
    }

    public function getBadActual() : array {
        return [
            [2],
            [1.1],
            [null],
            [true],
            [[]],
            [new \stdClass()]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }
}