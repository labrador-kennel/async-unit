<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay;

use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class BinaryVarExportAssertionComparisonDisplay implements AssertionComparisonDisplay {

    public function __construct(private mixed $a, private mixed $b) {}

    public function toString() : string {
        return sprintf(
            'comparing %s (%s) to %s (%s)',
            var_export($this->a, true),
            gettype($this->a),
            var_export($this->b, true),
            gettype($this->b)
        );
    }
}