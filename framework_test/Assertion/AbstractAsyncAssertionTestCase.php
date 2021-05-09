<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;
use PHPUnit\Framework\TestCase;

abstract class AbstractAsyncAssertionTestCase extends TestCase {

    abstract protected function getAssertion(mixed $expected, Promise|Generator|Coroutine $actual) : AsyncAssertion;

    abstract protected function getExpected() : mixed;

    abstract public function getGoodActual() : array;

    abstract public function getBadActual() : array;

    abstract protected function getSummaryAssertionMessageClass() : string;

    abstract protected function getDetailsAssertionMessageClass() : string;

    /**
     * @dataProvider getGoodActual
     */
    public function testAssertGoodValueEqualsGoodValue(mixed $actual) {
        Loop::run(function() use($actual) {
            $subject = $this->getAssertion($this->getExpected(), new Success($actual));
            $results = yield $subject->assert();

            $this->assertTrue($results->isSuccessful());
            $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
            $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
        });
    }

    /**
     * @dataProvider getBadActual
     */
    public function testAssertGoodValueDoesNotEqualBadValueInformation(mixed $actual) {
        Loop::run(function() use($actual) {
            $subject = $this->getAssertion($this->getExpected(), new Success($actual));
            $results = yield $subject->assert();

            $this->assertFalse($results->isSuccessful());
            $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
            $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
        });
    }

}