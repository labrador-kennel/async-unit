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

    protected function getAssertion($value) : Assertion {
        return new AssertIsFalse();
    }

    protected function getGoodValue() {
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
                return sprintf('Failed asserting that a value %s (%s) is false.', var_export($this->actual, true), gettype($this->actual));
            }
        };
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is false.', $actualType);
    }

    protected function getInvalidComparisonMessage($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}