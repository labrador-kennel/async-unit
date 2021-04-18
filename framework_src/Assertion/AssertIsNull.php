<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\NullAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertIsNull extends AbstractAssertionEquals implements Assertion {

    protected function isValidType(mixed $actual) : bool {
        return is_null($actual);
    }

    protected function getExpectedType() : string {
        return 'NULL';
    }

    protected function getExpected() {
        return null;
    }

    protected function getDefaultInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is null.', $actualType);
    }


    protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay {
        return new NullAssertionComparisonDisplay($actual);
    }
}