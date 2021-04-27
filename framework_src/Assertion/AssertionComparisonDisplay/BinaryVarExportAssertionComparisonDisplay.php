<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay;

use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

class BinaryVarExportAssertionComparisonDisplay implements AssertionComparisonDisplay {

    public function __construct(private mixed $a, private mixed $b) {}

    public function toString() : string {
        return sprintf(
            'asserting %s (%s) equals %s (%s)',
            var_export($this->a, true),
            gettype($this->a),
            var_export($this->b, true),
            gettype($this->b)
        );
    }

    public function toNotString() : string {
        return sprintf(
            'asserting %s (%s) does not equal %s (%s)',
            var_export($this->a, true),
            gettype($this->a),
            var_export($this->b, true),
            gettype($this->b)
        );
    }
}