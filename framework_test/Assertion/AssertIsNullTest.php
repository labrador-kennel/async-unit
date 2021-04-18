<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AssertIsNull
 */
class AssertIsNullTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonNullProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value) : Assertion {
        return new AssertIsNull();
    }

    protected function getGoodValue() {
        return null;
    }

    protected function getBadValue() {
        return 'not null';
    }

    protected function getExpectedType() {
        return 'NULL';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new class($actual) implements AssertionComparisonDisplay {

            public function __construct(private $actual) {}

            public function toString() : string {
                return sprintf('Failed asserting that a value %s (%s) is null.', var_export($this->actual, true), gettype($this->actual));
            }
        };
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is null.', $actualType);
    }

    protected function getInvalidComparisonMessage($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}