<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use PHPUnit\Framework\TestCase;

abstract class AbstractAssertionTestCase extends TestCase {

    use AssertionDataProvider;

    abstract protected function getAssertion($expected, $actual) : Assertion;

    abstract protected function getGoodValue();

    abstract protected function getBadValue();

    abstract protected function getExpectedType();

    abstract protected function getInvalidTypeAssertionMessageClass() : string;

    abstract protected function getSummaryAssertionMessageClass() : string;

    abstract protected function getDetailsAssertionMessageClass() : string;

    public function runBadTypeAssertions(mixed $actual) {
        $subject = $this->getAssertion($this->getGoodValue(), $actual);
        $results = $subject->assert();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf($this->getInvalidTypeAssertionMessageClass(), $results->getSummary());
        $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
    }

    public function testAssertGoodValueEqualsGoodValue() {
        $subject = $this->getAssertion($this->getGoodValue(), $this->getGoodValue());
        $results = $subject->assert();

        $this->assertTrue($results->isSuccessful());
        $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
        $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
    }

    public function testAssertGoodValueDoesNotEqualBadValueInformation() {
        $subject = $this->getAssertion($this->getGoodValue(), $this->getBadValue());
        $results = $subject->assert();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
        $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
    }

}