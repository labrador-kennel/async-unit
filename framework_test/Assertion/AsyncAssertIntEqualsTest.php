<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertFloatEquals
 */
class AsyncAssertIntEqualsTest extends AbstractAsyncAssertionTestCase {
    /**
     * @dataProvider nonIntProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected) : AsyncAssertion {
        return new AsyncAssertIntEquals($expected);
    }

    protected function getExpectedValue() {
        return 1;
    }

    protected function getBadValue() {
        return 2;
    }

    protected function getExpectedType() : string {
        return 'integer';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }

}