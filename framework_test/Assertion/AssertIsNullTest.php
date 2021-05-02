<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertIsNullTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonNullProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertIsNull($actual);
    }

    protected function getExpectedValue() {
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
                return sprintf('asserting %s (%s) is null', var_export($this->actual, true), gettype($this->actual));
            }

            public function toNotString() : string {
                // TODO: Implement toNotString() method.
            }
        };
    }

    protected function getInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is null.', $actualType);
    }

    protected function getAssertionString($actual) : string {
        return $this->getInvalidTypeMessage(gettype($actual));
    }
}