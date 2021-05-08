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

    public function getTestMethod() : string;

    public function getState() : TestState;

    /**
     * @return TestFailedException|AssertionFailedException|TestDisabledException|null
     */
    public function getException() : TestFailedException|AssertionFailedException|TestDisabledException|null;


}