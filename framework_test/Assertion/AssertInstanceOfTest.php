<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\InstanceOfMessage;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\TrueUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionMessage;
use Cspray\Labrador\AsyncUnit\Exception\Exception;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AssertInstanceOfTest extends TestCase {

    public function testPassExpectedStringNotClassThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The expected value must be a valid class but %s was given', var_export('not a class', true)
        ));
        new AssertInstanceOf('not a class', new \stdClass());
    }

    public function testInstanceOfInterfaceIsValid() {
        $subject = new AssertInstanceOf(AssertionMessage::class, new TrueUnaryOperandSummary('something'));
        $results = $subject->assert();

        $this->assertTrue($results->isSuccessful());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

    public function testInstanceOfTypeIsNotInstance() {
        $subject = new AssertInstanceOf(TestCase::class, new TrueUnaryOperandSummary('foo'));
        $results = $subject->assert();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

    public function testPassingObjectAsExpected() {
        $subject = new AssertInstanceOf(new Exception(), new InvalidArgumentException());
        $results = $subject->assert();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

}