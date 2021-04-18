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

    protected function getAssertion($value) : Assertion {
        return new AssertIsTrue();
    }

    protected function getGoodValue() {
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
                return sprintf('Failed asserting that a value %s (%s) is true.', var_export($this->actual, true), gettype($this->actual));
            }
        };
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is true.', $actualType);
    }

    protected function getInvalidComparisonMessage($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}