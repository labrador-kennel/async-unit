<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;
use PHPUnit\Framework\TestCase;

abstract class AbstractAsyncAssertionTestCase extends TestCase {

    use AssertionDataProvider;

    abstract protected function getAssertion(mixed $expected, Promise|Generator|Coroutine $actual) : AsyncAssertion;

    abstract protected function getExpectedValue() : mixed;

    abstract protected function getBadValue() : mixed;

    abstract protected function getExpectedType() : string;

    abstract protected function getInvalidTypeAssertionMessageClass() : string;

    abstract protected function getSummaryAssertionMessageClass() : string;

    abstract protected function getDetailsAssertionMessageClass() : string;

    public function runBadTypeAssertions(mixed $value, string $type) {
        Loop::run(function() use($value, $type) {
            $subject = $this->getAssertion($this->getExpectedValue(), new Success($value));
            $results = yield $subject->assert();

            $this->assertFalse($results->isSuccessful());
            $this->assertInstanceOf($this->getInvalidTypeAssertionMessageClass(), $results->getSummary());
            $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
        });
    }

    public function testAssertGoodValueEqualsGoodValue() {
        Loop::run(function() {
            $subject = $this->getAssertion($this->getExpectedValue(), new Success($this->getExpectedValue()));
            $results = yield $subject->assert();

            $this->assertTrue($results->isSuccessful());
            $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
            $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
        });
    }

    public function testAssertGoodValueDoesNotEqualBadValueInformation() {
        Loop::run(function() {
            $subject = $this->getAssertion($this->getExpectedValue(), new Success($this->getBadValue()));
            $results = yield $subject->assert();

            $this->assertFalse($results->isSuccessful());
            $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
            $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
        });
    }

}