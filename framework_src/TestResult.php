<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use Cspray\Labrador\AsyncUnit\Exception\TestDisabledException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;

/**
 * A type that is responsible for conveying the details about the processing of a specific test.
 *
 * @package Cspray\Labrador\AsyncUnit
 */
interface TestResult {

    /**
     * The TestCase that was created for the given test; please keep in mind that each test has its own TestCase created
     * and this object is unique per TestResult.
     *
     * @return TestCase
     */
    public function getTestCase() : TestCase;

    /**
     * Returns true whether the test was considered successful or not.
     *
     * @return bool
     */
    public function isSuccessful() : bool;

    /**
     * Returns true whether the test was disabled either as part of an annotation on the test, TestCase, or TestSuite or
     * as a result of the test calling `markDisabled()`.
     *
     * Please note that if this method return true the `getTestCase` implementation SHOULD throw an exception if called.
     *
     * @return bool
     */
    public function isDisabled() : bool;

    /**
     * @return TestFailedException|AssertionFailedException|TestDisabledException|null
     */
    public function getException() : TestFailedException|AssertionFailedException|TestDisabledException|null;

    public function getTestMethod() : string;

}