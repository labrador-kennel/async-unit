<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

use Throwable;

/**
 * Represents the data about a call to {@see Assertion::assert} or {@see AsyncAssertion::assert} that could be a
 * successful assertion or a failure.
 *
 *
 */
interface AssertionResult {

    public function isSuccessful() : bool;

    public function getErrorMessage() : string;

    public function getBacktrace() : array;

    public function getComparisonDisplay() : ?AssertionComparisonDisplay;

}