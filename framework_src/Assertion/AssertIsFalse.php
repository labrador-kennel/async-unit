<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\FalseAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\TrueAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionResult;

class AssertIsFalse extends AbstractAssertionEquals implements Assertion {

    protected function isValidType(mixed $actual) : bool {
        return is_bool($actual);
    }

    protected function getExpectedType() : string {
        return 'boolean';
    }

    protected function getExpected() {
        return false;
    }

    protected function getDefaultInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is false.', $actualType);
    }

    protected function getDefaultInvalidComparisonMessage($actual) : string {
        return $this->getDefaultInvalidTypeMessage(gettype($actual));
    }

    protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay {
        return new FalseAssertionComparisonDisplay($actual);
    }
}