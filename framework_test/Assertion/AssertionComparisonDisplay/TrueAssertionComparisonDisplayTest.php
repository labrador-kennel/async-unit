<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\TrueAssertionComparisonDisplay
 */
class TrueAssertionComparisonDisplayTest extends TestCase {

    public function testToString() {
        $display = new TrueAssertionComparisonDisplay('something');

        $this->assertSame("asserting 'something' (string) is true", $display->toString());
        $this->assertSame("asserting 'something' (string) is not true", $display->toNotString());
    }

}