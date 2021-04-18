<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertFloatEquals
 */
class AsyncAssertFloatEqualsTest extends AbstractAsyncAssertionTestCase {
    /**
     * @dataProvider nonFloatProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected) : AsyncAssertion {
        return new AsyncAssertFloatEquals($expected);
    }

    protected function getExpectedValue() {
        return 3.14;
    }

    protected function getBadValue() {
        return 9.99;
    }

    protected function getExpectedType() : string {
        return 'double';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }

}