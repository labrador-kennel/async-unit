<?php

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use PHPUnit\Framework\TestCase;

class AssertStringEqualsTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonStringProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertStringEquals($value, $actual);
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
