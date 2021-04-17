<?php

namespace Cspray\Labrador\AsyncTesting;

use Cspray\Labrador\AsyncTesting\Exception\TestFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncTesting\AssertionContext
 */
class AssertionContextTest extends TestCase {

    private AssertionContext $subject;

    public function setUp() : void {
        parent::setUp();
        $reflectedClass = new \ReflectionClass(AssertionContext::class);
        $this->subject = $reflectedClass->newInstanceWithoutConstructor();
    }

    public function testAssertStringEqualsHasNoSideEffects() {
        $this->expectNotToPerformAssertions();
        $this->subject->stringEquals('foo', 'foo');
    }

    public function testAssertStringNotEqualThrowsException() {
        $this->expectException(TestFailedException::class);
        $this->expectExceptionMessage("Failed comparing that 2 strings are equal to one another\nFailed comparing 'foo' (string) to 'bar' (string)");

        $this->subject->stringEquals('foo', 'bar');
    }

    public function testAssertStringNotEqualCustomMessage() {
        $this->expectException(TestFailedException::class);
        $this->expectExceptionMessage("my custom error message\nFailed comparing 'foo' (string) to 'bar' (string)");

        $this->subject->stringEquals('foo', 'bar', 'my custom error message');
    }

    public function testAssertStringSuccessIncrementsAssertionCount() {
        $this->assertEquals(0, $this->subject->getAssertionCount());
        $this->subject->stringEquals('foo', 'foo');
        $this->assertEquals(1, $this->subject->getAssertionCount());
        $this->subject->stringEquals('bar', 'bar');
        $this->assertEquals(2, $this->subject->getAssertionCount());
    }

    public function testAssertFailureIncrementsAssertionCount() {
        $this->assertEquals(0, $this->subject->getAssertionCount());
        $this->subject->stringEquals('foo', 'foo');
        $this->assertEquals(1, $this->subject->getAssertionCount());
        $this->subject->stringEquals('bar', 'bar');
        $this->assertEquals(2, $this->subject->getAssertionCount());
        try {
            $this->subject->stringEquals('baz', 'foo');
        } catch (TestFailedException $_) {

        } finally {
            $this->assertEquals(3, $this->subject->getAssertionCount());
        }
    }


}
