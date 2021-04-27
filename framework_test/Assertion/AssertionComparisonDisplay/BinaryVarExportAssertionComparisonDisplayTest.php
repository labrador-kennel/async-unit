<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay
 */
class BinaryVarExportAssertionComparisonDisplayTest extends TestCase {

    public function testToString() {
        $display = new BinaryVarExportAssertionComparisonDisplay('foo', 'bar');

        $this->assertSame("asserting 'foo' (string) equals 'bar' (string)", $display->toString());
        $this->assertSame("asserting 'foo' (string) does not equal 'bar' (string)", $display->toNotString());
    }

}