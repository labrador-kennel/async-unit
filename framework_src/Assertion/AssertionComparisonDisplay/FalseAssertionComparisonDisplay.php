<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay;

use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class FalseAssertionComparisonDisplay implements AssertionComparisonDisplay {
    public function __construct(private $actual) {}

    public function toString() : string {
        return sprintf('asserting %s (%s) is false', var_export($this->actual, true), gettype($this->actual));
    }

    public function toNotString() : string {
        return sprintf('asserting %s (%s) is not false', var_export($this->actual, true), gettype($this->actual));
    }
}