<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use PHPUnit\Framework\TestCase;

abstract class AbstractAsyncAssertionTestCase extends TestCase {

    use AssertionDataProvider;

    abstract protected function getAssertion($expected) : AsyncAssertion;

    abstract protected function getExpectedValue();

    abstract protected function getBadValue();

    abstract protected function getExpectedType() : string;

    abstract protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay;

    protected function getInvalidComparisonMessage($actual) : string {
        return 'Failed comparing that 2 ' . $this->getExpectedType() . 's are equal to one another';
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf(
            'Failed asserting that a value with type "%s" is comparable to type "%s".', $actualType, $this->getExpectedType()
        );
    }

    public function runBadTypeAssertions(mixed $value, string $type) {
        Loop::run(function() use($value, $type) {
            $subject = $this->getAssertion($this->getExpectedValue());
            $results = yield $subject->assert(new Success($value));

            $this->assertFalse($results->isSuccessful());
            $this->assertSame($this->getInvalidTypeMessage(gettype($value)), $results->getErrorMessage());
            $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $value)->toString(), $results->getComparisonDisplay()->toString());
        });
    }

    public function testAssertGoodValueEqualsGoodValue() {
        Loop::run(function() {
            $subject = $this->getAssertion($this->getExpectedValue());
            $results = yield $subject->assert(new Success($this->getExpectedValue()));

            $this->assertTrue($results->isSuccessful());
            $this->assertNull($results->getErrorMessage());
            $this->assertNull($results->getComparisonDisplay());
        });
    }

    public function testAssertGoodValueDoesNotEqualBadValueInformation() {
        Loop::run(function() {
            $subject = $this->getAssertion($this->getExpectedValue());
            $results = yield $subject->assert(new Success($this->getBadValue()));

            $this->assertFalse($results->isSuccessful());
            $this->assertSame($this->getInvalidComparisonMessage($this->getBadValue()), $results->getErrorMessage());
            $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $this->getBadValue())->toString(), $results->getComparisonDisplay()->toString());
        });
    }

    public function testCustomMessageUsedIfProvided() {
        Loop::run(function() {
            $subject = $this->getAssertion($this->getExpectedValue());
            $results = yield $subject->assert(new Success($this->getBadValue()), 'my custom message');

            $this->assertFalse($results->isSuccessful());
            $this->assertSame('my custom message', $results->getErrorMessage());
            $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $this->getBadValue())->toString(), $results->getComparisonDisplay()->toString());
        });
    }

}