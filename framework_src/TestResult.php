<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;

interface TestResult {
    public function getTestCase() : TestCase;

    public function getTestMethod() : string;

    public function isSuccessful() : bool;

    public function getFailureException() : TestFailedException|AssertionFailedException;
}