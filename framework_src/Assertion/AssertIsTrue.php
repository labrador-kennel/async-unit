<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\TrueAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionResult;

class AssertIsTrue extends AbstractAssertionEquals implements Assertion {

    public function __construct(private mixed $actual) {}

    protected function isValidType(mixed $actual) : bool {
        return is_bool($actual);
    }

    protected function getExpectedType() : string {
        return 'boolean';
    }

    protected function getExpected() : bool {
        return true;
    }

    protected function getActual() : mixed {
        return $this->actual;
    }

    protected function getInvalidTypeAssertionString(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is true.', $actualType);
    }

    protected function getAssertionString($actual) : string {
        return $this->getInvalidTypeAssertionString(gettype($actual));
    }

    protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay {
        return new TrueAssertionComparisonDisplay($actual);
    }
}