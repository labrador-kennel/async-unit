<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

use Cspray\Labrador\AsyncTesting\Exception\TestFailedException;

interface TestResult {
    public function getTestCase() : TestCase;

    public function getTestMethod() : string;

    public function isSuccessful() : bool;

    public function getFailureException() : TestFailedException;

    public function getAssertionComparisonDisplay() : ?AssertionComparisonDisplay;
}