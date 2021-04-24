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

    abstract protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay;

    protected function getAssertionString($actual) : string {
        return 'comparing that 2 ' . $this->getExpectedType() . 's are equal to one another';
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf(
            'asserting that a value with type "%s" is comparable to type "%s".', $actualType, $this->getExpectedType()
        );
    }

    public function runBadTypeAssertions(mixed $value, string $type) {
        Loop::run(function() use($value, $type) {
            $subject = $this->getAssertion($this->getExpectedValue(), new Success($value));
            $results = yield $subject->assert();

            $this->assertFalse($results->isSuccessful());
            $this->assertSame($this->getInvalidTypeMessage(gettype($value)), $results->getAssertionString());
            $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $value)->toString(), $results->getComparisonDisplay()->toString());
        });
    }

    public function testAssertGoodValueEqualsGoodValue() {
        Loop::run(function() {
            $subject = $this->getAssertion($this->getExpectedValue(), new Success($this->getExpectedValue()));
            $results = yield $subject->assert();

            $this->assertTrue($results->isSuccessful());
            $this->assertSame($this->getAssertionString($this->getExpectedValue()), $results->getAssertionString());
            $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $this->getExpectedValue())->toString(), $results->getComparisonDisplay()->toString());
        });
    }

    public function testAssertGoodValueDoesNotEqualBadValueInformation() {
        Loop::run(function() {
            $subject = $this->getAssertion($this->getExpectedValue(), new Success($this->getBadValue()));
            $results = yield $subject->assert();

            $this->assertFalse($results->isSuccessful());
            $this->assertSame($this->getAssertionString($this->getBadValue()), $results->getAssertionString());
            $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $this->getBadValue())->toString(), $results->getComparisonDisplay()->toString());
        });
    }

}