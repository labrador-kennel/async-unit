<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay;


use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class FalseAssertionComparisonDisplay implements AssertionComparisonDisplay {
    public function __construct(private $actual) {}

    public function toString() : string {
        return sprintf('Failed asserting that a value %s (%s) is false.', var_export($this->actual, true), gettype($this->actual));
    }

}