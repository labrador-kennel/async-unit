<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\NullAssertionComparisonDisplay
 */
class NullAssertionComparisonDisplayTest extends TestCase {

    public function testToString() {
        $display = new NullAssertionComparisonDisplay('something else');

        $this->assertSame("asserting 'something else' (string) is null", $display->toString());
        $this->assertSame("asserting 'something else' (string) is not null", $display->toNotString());
    }


}