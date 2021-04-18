<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\FalseAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsFalse
 */
class AsyncAssertIsFalseTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonBoolProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected) : AsyncAssertion {
        return new AsyncAssertIsFalse($expected);
    }

    protected function getExpectedValue() {
        return false;
    }

    protected function getBadValue() {
        return true;
    }

    protected function getExpectedType() : string {
        return 'boolean';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new FalseAssertionComparisonDisplay($actual);
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is false.', $actualType);
    }

    protected function getInvalidComparisonMessage($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}