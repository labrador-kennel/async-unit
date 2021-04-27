<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertIsNull
 */
class AssertIsFalseTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonBoolProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertIsFalse($actual);
    }

    protected function getExpectedValue() {
        return false;
    }

    protected function getBadValue() {
        return true;
    }

    protected function getExpectedType() {
        return 'boolean';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new class($actual) implements AssertionComparisonDisplay {

            public function __construct(private $actual) {}

            public function toString() : string {
                return sprintf('asserting %s (%s) is false', var_export($this->actual, true), gettype($this->actual));
            }

            public function toNotString() : string {

            }
        };
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is false.', $actualType);
    }

    protected function getAssertionString($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}