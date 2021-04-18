<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Loop;
use Amp\Success;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertStringEquals
 */
class AsyncAssertStringEqualsTest extends TestCase {

    public function nonStringProvider() : array {
        return [
            [4321, 'integer'],
            [3.14, 'double'],
            [false, 'boolean'],
            [[1,2,3], 'array'],
            [new \stdClass(), 'object']
        ];
    }

    /**
     * @dataProvider nonStringProvider
     */
    public function testAssertNotStringFailedAssertionResult(mixed $value, string $type) {
        Loop::run(function() use($value, $type) {
            $subject = new AsyncAssertStringEquals('foo');
            $results = yield $subject->assert(new Success($value));

            $this->assertFalse($results->isSuccessful());
            $this->assertSame(sprintf(
                'Failed asserting that a value with type "%s" is comparable to type "string".', $type
            ), $results->getErrorMessage());
            $this->assertSame(sprintf(
                'Failed comparing \'foo\' (string) to %s (%s)', var_export($value, true), $type
            ), $results->getComparisonDisplay()->toString());
        });
    }

    public function testAssertStringEquals() {
        Loop::run(function() {
            $subject = new AsyncAssertStringEquals('foo');
            $results = yield $subject->assert(new Success('foo'));

            $this->assertTrue($results->isSuccessful());
            $this->assertNull($results->getErrorMessage());
            $this->assertNull($results->getComparisonDisplay());
        });
    }

    public function testAssertStringNotEquals() {
        Loop::run(function() {
            $subject = new AsyncAssertStringEquals('foo');
            $results = yield $subject->assert(new Success('bar'));

            $this->assertFalse($results->isSuccessful());
            $this->assertSame('Failed comparing that 2 strings are equal to one another', $results->getErrorMessage());
            $this->assertSame('Failed comparing \'foo\' (string) to \'bar\' (string)', $results->getComparisonDisplay()->toString());
        });
    }

    public function testCustomMessageUsedIfProvided() {
        Loop::run(function() {
            $subject = new AsyncAssertStringEquals('foo');
            $results = yield $subject->assert(new Success('bar'), 'my custom message');

            $this->assertFalse($results->isSuccessful());
            $this->assertSame('my custom message', $results->getErrorMessage());
            $this->assertSame('Failed comparing \'foo\' (string) to \'bar\' (string)', $results->getComparisonDisplay()->toString());
        });

    }

}