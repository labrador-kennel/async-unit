<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\NullAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertIsNull extends AbstractAssertionEquals implements Assertion {

    public function __construct(private mixed $actual) {}

    protected function isValidType(mixed $actual) : bool {
        return is_null($actual);
    }

    protected function getExpectedType() : string {
        return 'NULL';
    }

    protected function getExpected() : mixed {
        return null;
    }

    protected function getActual() : mixed {
        return $this->actual;
    }

    protected function getInvalidTypeAssertionString(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is null.', $actualType);
    }

    protected function getAssertionString($actual) : string {
        return $this->getInvalidTypeAssertionString(gettype($actual));
    }

    protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay {
        return new NullAssertionComparisonDisplay($actual);
    }
}