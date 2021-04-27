<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertIsNull
 */
class AssertIsTrueTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonBoolProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertIsTrue($actual);
    }

    protected function getExpectedValue() {
        return true;
    }

    protected function getBadValue() {
        return false;
    }

    protected function getExpectedType() {
        return 'boolean';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new class($actual) implements AssertionComparisonDisplay {

            public function __construct(private $actual) {}

            public function toString() : string {
                return sprintf('asserting %s (%s) is true', var_export($this->actual, true), gettype($this->actual));
            }

            public function toNotString() : string {
                // TODO: Implement toNotString() method.
            }
        };
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is true.', $actualType);
    }

    protected function getAssertionString($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}