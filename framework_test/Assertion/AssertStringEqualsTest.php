<?php

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertStringEquals
 */
class AssertStringEqualsTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonStringProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value) : Assertion {
        return new AssertStringEquals($value);
    }

    protected function getExpectedValue() {
        return 'async unit';
    }

    protected function getBadValue() {
        return 'blocking code';
    }

    protected function getExpectedType() {
        return 'string';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }

}
