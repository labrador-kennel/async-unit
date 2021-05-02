<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;


use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertArrayEqualsTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonArrayProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertArrayEquals(['a', 'b', 'c'], $actual);
    }

    protected function getExpectedValue() {
        return ['a', 'b', 'c'];
    }

    protected function getBadValue() {
        return ['z', 'x', 'y'];
    }

    protected function getExpectedType() {
        return 'array';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }

}