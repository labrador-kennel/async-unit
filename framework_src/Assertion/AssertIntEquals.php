<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertIntEquals extends AbstractAssertionEquals implements Assertion {

    public function __construct(private int $expected, private mixed $actual) {}

    protected function isValidType(mixed $actual) : bool {
        return is_integer($actual);
    }

    protected function getExpectedType() : string {
        return 'integer';
    }

    protected function getExpected() : int {
        return $this->expected;
    }

    protected function getActual() : mixed {
        return $this->actual;
    }

    protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($this->getExpected(), $actual);
    }
}