<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\InstanceOfMessage;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\TrueUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionMessage;
use Cspray\Labrador\AsyncUnit\Exception\Exception;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use PHPUnit\Framework\TestCase;

class AsyncAssertInstanceOfTest extends TestCase {

    public function testPassExpectedStringNotClassThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The expected value must be a valid class but %s was given', var_export('not a class', true)
        ));
        new AsyncAssertInstanceOf('not a class', new Success(new \stdClass()));
    }

    public function testInstanceOfInterfaceIsValid() {
        Loop::run(function() {
            $subject = new AsyncAssertInstanceOf(AssertionMessage::class, new Success(new TrueUnaryOperandSummary('something')));
            $results = yield $subject->assert();

            $this->assertTrue($results->isSuccessful());
            $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
            $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
        });
    }

    public function testInstanceOfTypeIsNotInstance() {
        Loop::run(function() {
            $subject = new AsyncAssertInstanceOf(TestCase::class, new Success(new TrueUnaryOperandSummary('foo')));
            $results = yield $subject->assert();

            $this->assertFalse($results->isSuccessful());
            $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
            $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
        });
    }

    public function testPassingObjectAsExpected() {
        Loop::run(function() {
            $subject = new AsyncAssertInstanceOf(new InvalidStateException(), new Success(new InvalidArgumentException()));
            $results = yield $subject->assert();

            $this->assertFalse($results->isSuccessful());
            $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
            $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
        });
    }

}