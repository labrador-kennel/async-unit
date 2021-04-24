<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

/**
 * A type that represents an individual assertion in a #[Test], 1-or-many Assertion may take place in a #[Test].
 *
 * An Assertion is expected to work on "resolved" values. That is values that are not reliant on a Promise or Generator
 * or Coroutine completing or running to conclusion. Passing a Promise or Generator or Coroutine to one of these
 * Assertions is HIGHLY indicative of a logical error where you're misapplying which Assertion context to use.
 *
 * Although the method for running an assertion type hints against a mixed value Assertion implementations SHOULD BE
 * type-safe as much as possible. For example, we should avoid creating Assertions such as
 * assertEquals(mixed $expected, mixed $actual) and allowing PHP type coercion to do the appropriate type juggling.
 * Instead favor a more explicit API that ensures the type-safety for the comparison is directly in the helper method.
 * i.e. assertStringEquals(string $expected, string $actual)
 */
interface Assertion {

    /**
     * Run an assertion on $actual and return an AssertionResult detailing whether the assertion succeeded or failed.
     *
     * The $actual value should always correspond to the $actual value in the assert* methods in the AssertionContext
     * object. If $errorMessage is not provided a reasonable default should be provided. Please ensure that you review
     * the AssertionResult to understand what is expected before implementing your own Assertion. It is likely advisable
     * that you extend from AbstractAssertion and read the User Guide on creating your own Assertion.
     *
     * You SHOULD NOT throw an Exception from this method to signify that the Assertion failed. An assertion potentially
     * failing is, and should be, expected. We should avoid throwing Exceptions in places that aren't exceptional, and
     * something we expect to happen isn't exceptional. The only Exceptions thrown from this method should be ones that
     * truly represent the Assertion cannot be processed because of something that was not, or should not, be accounted
     * for.
     *
     * @return AssertionResult
     */
    public function assert() : AssertionResult;

}