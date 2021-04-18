<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\FalseAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\NullAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsFalse
 */
class AsyncAssertIsNullTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonNullProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected) : AsyncAssertion {
        return new AsyncAssertIsNull($expected);
    }

    protected function getExpectedValue() {
        return null;
    }

    protected function getBadValue() {
        return 'non null';
    }

    protected function getExpectedType() : string {
        return 'NULL';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new NullAssertionComparisonDisplay($actual);
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is null.', $actualType);
    }

    protected function getInvalidComparisonMessage($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}