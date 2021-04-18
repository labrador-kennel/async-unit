<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\FalseAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\TrueAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsFalse
 */
class AsyncAssertIsTrueTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonBoolProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected) : AsyncAssertion {
        return new AsyncAssertIsTrue($expected);
    }

    protected function getExpectedValue() {
        return true;
    }

    protected function getBadValue() {
        return false;
    }

    protected function getExpectedType() : string {
        return 'boolean';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new TrueAssertionComparisonDisplay($actual);
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is true.', $actualType);
    }

    protected function getInvalidComparisonMessage($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}