<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

/**
 * Represents the data about a call to {@see Assertion::assert} or {@see AsyncAssertion::assert} that could be a
 * successful assertion or a failure.
 *
 * AssertionResult SHOULD take the stance that "no news is good news". If the Assertion was successful the only value
 * that MUST be provide is the successful boolean check. All other values SHOULD not be provided in success conditions.
 * We take the opposite approach that if there is bad news we want to be well informed. If the Assertion was NOT
 * successful ALL values SHOULD be provided with maximum detail into how the Assertion failed. To help ensure the
 * appropriate state is created AssertionResult should almost always be created by AssertionFactory.
 */
interface AssertionResult {

    public function isSuccessful() : bool;

    public function getErrorMessage() : ?string;

    public function getComparisonDisplay() : ?AssertionComparisonDisplay;

}