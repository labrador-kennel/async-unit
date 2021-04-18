<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionResult;

class AssertFloatEquals extends AbstractAssertionEquals implements Assertion {

    public function __construct(private float $expected) {}

    protected function isValidType(mixed $actual) : bool {
        return is_float($actual);
    }

    protected function getExpectedType() : string {
        return 'double';
    }

    protected function getExpected() : float {
        return $this->expected;
    }

    protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($this->getExpected(), $actual);
    }
}