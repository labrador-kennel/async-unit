<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

use Cspray\Labrador\AsyncTesting\Context\AssertionContext;
use Cspray\Labrador\AsyncTesting\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncTesting\Exception\TestFailedException;

interface TestResult {
    public function getTestCase() : TestCase;

    public function getTestMethod() : string;

    public function isSuccessful() : bool;

    public function hasAnyAssertions() : bool;

    public function getAssertionContext() : AssertionContext;

    public function getAsyncAssertionContext() : AsyncAssertionContext;

    public function getFailureException() : TestFailedException;

    public function getAssertionComparisonDisplay() : AssertionComparisonDisplay;
}