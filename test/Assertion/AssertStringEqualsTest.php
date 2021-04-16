<?php

namespace Cspray\Labrador\AsyncTesting\Assertion;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncTesting\Assertion\AssertStringEquals
 */
class AssertStringEqualsTest extends TestCase {

    public function testAssertNotStringFailedAssertionResult() {
        $subject = new AssertStringEquals('foo');
        $results = $subject->assert(1234);

        $this->assertFalse($results->isSuccessful());
        $this->assertSame('Failed asserting that a value with type "int" is comparable to type "string".', $results->getErrorMessage());
        $this->assertSame(
            'Failed comparing 1234 (int) to incompatible type foo (string)',
            $results->getComparisonDisplay()->toString()
        );
    }



}
