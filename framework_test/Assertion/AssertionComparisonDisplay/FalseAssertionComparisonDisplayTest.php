<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\FalseAssertionComparisonDisplay
 */
class FalseAssertionComparisonDisplayTest extends TestCase {

    public function testToString() {
        $display = new FalseAssertionComparisonDisplay('something');

        $this->assertSame("asserting 'something' (string) is false", $display->toString());
        $this->assertSame("asserting 'something' (string) is not false", $display->toNotString());
    }

}