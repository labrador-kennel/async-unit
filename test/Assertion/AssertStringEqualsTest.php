<?php

namespace Cspray\Labrador\AsyncTesting\Assertion;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncTesting\Assertion\AssertStringEquals
 */
class AssertStringEqualsTest extends TestCase {

    public function nonStringProvider() : array {
        return [
            [1234, 'integer'],
            [9876.54, 'double'],
            [true, 'boolean'],
            [[], 'array'],
            [new \stdClass(), 'object']
        ];
    }

    /**
     * @dataProvider nonStringProvider
     */
    public function testAssertNotStringFailedAssertionResult(mixed $value, string $type) {
        $subject = new AssertStringEquals('foo');
        $results = $subject->assert($value);

        $this->assertFalse($results->isSuccessful());
        $this->assertSame(sprintf(
            'Failed asserting that a value with type "%s" is comparable to type "string".', $type
        ), $results->getErrorMessage());
        $this->assertSame(sprintf(
            'Failed comparing \'foo\' (string) to %s (%s)', var_export($value, true), $type
        ), $results->getComparisonDisplay()->toString());
    }

    public function testAssertStringEquals() {
        $subject = new AssertStringEquals('foo');
        $results = $subject->assert('foo');

        $this->assertTrue($results->isSuccessful());
        $this->assertNull($results->getErrorMessage());
        $this->assertNull($results->getComparisonDisplay());
    }

    public function testAssertStringNotEquals() {
        $subject = new AssertStringEquals('foo');
        $results = $subject->assert('bar');

        $this->assertFalse($results->isSuccessful());
        $this->assertSame('Failed comparing that 2 strings are equal to one another', $results->getErrorMessage());
        $this->assertSame('Failed comparing \'foo\' (string) to \'bar\' (string)', $results->getComparisonDisplay()->toString());
    }

    public function testCustomMessageUsedIfProvided() {
        $subject = new AssertStringEquals('foo');
        $results = $subject->assert('bar', 'my custom message');

        $this->assertFalse($results->isSuccessful());
        $this->assertSame('my custom message', $results->getErrorMessage());
        $this->assertSame('Failed comparing \'foo\' (string) to \'bar\' (string)', $results->getComparisonDisplay()->toString());

    }

}
