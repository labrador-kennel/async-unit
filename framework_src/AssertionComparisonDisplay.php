<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

/**
 * A type that represents how the $expected and $actual values comparisons differ in a failed AssertionResult.
 *
 * It is expected that default implementations of this will have output similar to var_export(). However, it is also
 * expected that more advanced assertions will require more complex comparison display.
 */
interface AssertionComparisonDisplay {

    /**
     * A text representation of how the Assertion failed comparing values.
     *
     * @return string
     */
    public function toString() : string;

    public function toNotString() : string;

}