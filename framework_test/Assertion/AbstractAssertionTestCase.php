<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;


use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use PHPUnit\Framework\TestCase;

abstract class AbstractAssertionTestCase extends TestCase {

    use AssertionDataProvider;

    abstract protected function getAssertion($expected) : Assertion;

    abstract protected function getExpectedValue();

    abstract protected function getBadValue();

    abstract protected function getExpectedType();

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
        $subject = $this->getAssertion($this->getExpectedValue());
        $results = $subject->assert($value);

        $this->assertFalse($results->isSuccessful());
        $this->assertSame($this->getInvalidTypeMessage(gettype($value)), $results->getAssertionString());
        $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $value)->toString(), $results->getComparisonDisplay()->toString());
    }

    public function testAssertGoodValueEqualsGoodValue() {
        $subject = $this->getAssertion($this->getExpectedValue());
        $results = $subject->assert($this->getExpectedValue());

        $this->assertTrue($results->isSuccessful());
        $this->assertSame($this->getAssertionString($this->getExpectedValue()), $results->getAssertionString());
        $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $this->getExpectedValue())->toString(), $results->getComparisonDisplay()->toString());
    }

    public function testAssertGoodValueDoesNotEqualBadValueInformation() {
        $subject = $this->getAssertion($this->getExpectedValue());
        $results = $subject->assert($this->getBadValue());

        $this->assertFalse($results->isSuccessful());
        $this->assertSame($this->getAssertionString($this->getBadValue()), $results->getAssertionString());
        $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getExpectedValue(), $this->getBadValue())->toString(), $results->getComparisonDisplay()->toString());
    }

}