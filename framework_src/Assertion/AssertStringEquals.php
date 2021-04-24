<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionResult;

class AssertStringEquals extends AbstractAssertionEquals implements Assertion {

    public function __construct(private string $expected, private mixed $actual) {}

    protected function isValidType(mixed $actual) : bool {
        return is_string($actual);
    }

    protected function getExpectedType() : string {
        return 'string';
    }

    protected function getExpected() : string {
        return $this->expected;
    }

    protected function getActual() : mixed {
        return $this->actual;
    }

    protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($this->getExpected(), $actual);
    }
}