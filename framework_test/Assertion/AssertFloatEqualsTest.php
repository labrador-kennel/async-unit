<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertFloatEquals
 */
class AssertFloatEqualsTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonFloatProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value) : Assertion {
        return new AssertFloatEquals($value);
    }

    protected function getExpectedValue() {
        return 9876.54;
    }

    protected function getBadValue() {
        return 1234.56;
    }

    protected function getExpectedType() {
        return 'double';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }
}