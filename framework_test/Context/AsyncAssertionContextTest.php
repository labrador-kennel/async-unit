<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Context;


use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;

/**
 * @covers \Cspray\Labrador\AsyncUnit\AsyncAssertionContext
 */
class AsyncAssertionContextTest extends \PHPUnit\Framework\TestCase {


    private AsyncAssertionContext $subject;

    public function setUp() : void {
        parent::setUp();
        $reflectedClass = new \ReflectionClass(AsyncAssertionContext::class);
        $this->subject = $reflectedClass->newInstanceWithoutConstructor();
    }

    public function testAssertStringEqualsHasNoSideEffects() {
        Loop::run(function() {
            $this->expectNotToPerformAssertions();
            yield $this->subject->stringEquals('foo', new Success('foo'));
        });
    }

    public function testAssertStringNotEqualThrowsException() {
        Loop::run(function() {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionMessage("Failed comparing that 2 strings are equal to one another");

            yield $this->subject->stringEquals('foo', new Success('bar'));
        });
    }

    public function testAssertStringNotEqualCustomMessage() {
        Loop::run(function() {
            $this->expectException(AssertionFailedException::class);
            $this->expectExceptionMessage("my custom error message");

            yield $this->subject->stringEquals('foo', new Success('bar'), 'my custom error message');
        });
    }

    public function testAssertStringSuccessIncrementsAssertionCount() {
        Loop::run(function() {
            $this->assertEquals(0, $this->subject->getAssertionCount());
            yield $this->subject->stringEquals('foo', new Success('foo'));
            $this->assertEquals(1, $this->subject->getAssertionCount());
            yield $this->subject->stringEquals('bar', new Success('bar'));
            $this->assertEquals(2, $this->subject->getAssertionCount());
        });
    }

    public function testAssertFailureIncrementsAssertionCount() {
        Loop::run(function() {
            $this->assertEquals(0, $this->subject->getAssertionCount());
            yield $this->subject->stringEquals('foo', new Success('foo'));
            $this->assertEquals(1, $this->subject->getAssertionCount());
            yield $this->subject->stringEquals('bar', new Success('bar'));
            $this->assertEquals(2, $this->subject->getAssertionCount());
            try {
                yield $this->subject->stringEquals('baz', new Success('foo'));
            } catch (TestFailedException $_) {

            } finally {
                $this->assertEquals(3, $this->subject->getAssertionCount());
            }
        });
    }


}