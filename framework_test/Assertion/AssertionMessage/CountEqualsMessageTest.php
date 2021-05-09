<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use Cspray\Labrador\AsyncUnit\Stub\CountableStub;
use PHPUnit\Framework\TestCase;

class CountEqualsMessageTest extends TestCase {

    public function testToStringArray() {
        $message = new CountEqualsMessage(5, [1, 2, 3, 4]);
        $expected = 'asserting array with count of 4 equals expected count of 5';
        $this->assertSame($expected, $message->toString());
    }

    public function testToStringObject() {
        $message = new CountEqualsMessage(20, new CountableStub(6));
        $expected = sprintf('asserting %s with count of 6 equals expected count of 20', CountableStub::class);
        $this->assertSame($expected, $message->toString());
    }

    public function testToNotStringArray() {
        $message = new CountEqualsMessage(5, [1, 2, 3, 4]);
        $expected = 'asserting array with count of 4 does not equal expected count of 5';
        $this->assertSame($expected, $message->toNotString());
    }

    public function testToNotStringObject() {
        $message = new CountEqualsMessage(20, new CountableStub(6));
        $expected = sprintf('asserting %s with count of 6 does not equal expected count of 20', CountableStub::class);
        $this->assertSame($expected, $message->toNotString());
    }

}