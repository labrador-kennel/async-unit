<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use Cspray\Labrador\AsyncUnit\AssertionMessage;
use PHPUnit\Framework\TestCase;

class InstanceOfSummaryTest extends TestCase {

    public function testToStringExpectedIsString() {
        $instanceOfSummaryMessage = new InstanceOfMessage(AssertionMessage::class, new \stdClass());
        $expected = sprintf(
            'asserting object with type "stdClass" is an instanceof %s',
            AssertionMessage::class
        );
        $this->assertSame($expected, $instanceOfSummaryMessage->toString());
    }

    public function testToStringExpectedIsObject() {
        $instanceOfSummaryMessage = new InstanceOfMessage($this, new \stdClass());
        $expected = sprintf(
            'asserting object with type "stdClass" is an instanceof %s',
            $this::class
        );
        $this->assertSame($expected, $instanceOfSummaryMessage->toString());
    }

    public function testToNotStringExpectedIsString() {
        $instanceOfSummaryMessage = new InstanceOfMessage(AssertionMessage::class, new \stdClass());
        $expected = sprintf(
            'asserting object with type "stdClass" is not an instanceof %s',
            AssertionMessage::class
        );
        $this->assertSame($expected, $instanceOfSummaryMessage->toNotString());
    }

    public function testToNotStringExpectedIsObject() {
        $instanceOfSummaryMessage = new InstanceOfMessage($this, new \stdClass());
        $expected = sprintf(
            'asserting object with type "stdClass" is not an instanceof %s',
            $this::class
        );
        $this->assertSame($expected, $instanceOfSummaryMessage->toNotString());
    }

}