<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class AssertArrayEquals extends AbstractAssertionEquals implements Assertion {

    public function __construct(private array $expected) {}

    protected function isValidType(mixed $actual) : bool {
        return is_array($actual);
    }

    protected function getExpectedType() : string {
        return 'array';
    }

    protected function getExpected() {
        return $this->expected;
    }

    protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay {
        return new Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay($this->getExpected(), $actual);
    }
}