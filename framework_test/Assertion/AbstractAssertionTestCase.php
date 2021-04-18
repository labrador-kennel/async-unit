<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;


use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use PHPUnit\Framework\TestCase;

abstract class AbstractAssertionTestCase extends TestCase {

    use AssertionDataProvider;

    abstract protected function getAssertion($value) : Assertion;

    abstract protected function getGoodValue();

    abstract protected function getBadValue();

    abstract protected function getExpectedType();

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
        $subject = $this->getAssertion($this->getGoodValue());
        $results = $subject->assert($value);

        $this->assertFalse($results->isSuccessful());
        $this->assertSame($this->getInvalidTypeMessage(gettype($value)), $results->getErrorMessage());
        $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getGoodValue(), $value)->toString(), $results->getComparisonDisplay()->toString());
    }

    public function testAssertGoodValueEqualsGoodValue() {
        $subject = $this->getAssertion($this->getGoodValue());
        $results = $subject->assert($this->getGoodValue());

        $this->assertTrue($results->isSuccessful());
        $this->assertNull($results->getErrorMessage());
        $this->assertNull($results->getComparisonDisplay());
    }

    public function testAssertGoodValueDoesNotEqualBadValueInformation() {
        $subject = $this->getAssertion($this->getGoodValue());
        $results = $subject->assert($this->getBadValue());

        $this->assertFalse($results->isSuccessful());
        $this->assertSame($this->getInvalidComparisonMessage($this->getBadValue()), $results->getErrorMessage());
        $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getGoodValue(), $this->getBadValue())->toString(), $results->getComparisonDisplay()->toString());
    }

    public function testCustomMessageUsedIfProvided() {
        $subject = $this->getAssertion($this->getGoodValue());
        $results = $subject->assert($this->getBadValue(), 'my custom message');

        $this->assertFalse($results->isSuccessful());
        $this->assertSame('my custom message', $results->getErrorMessage());
        $this->assertSame($this->getExpectedAssertionComparisonDisplay($this->getGoodValue(), $this->getBadValue())->toString(), $results->getComparisonDisplay()->toString());
    }


}