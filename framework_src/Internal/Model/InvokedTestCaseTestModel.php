<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal\Model;

use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\TestCase;

/**
 * @internal
 */
class InvokedTestCaseTestModel {

    public function __construct(
        private TestCase $testCase,
        private string $method,
        private int $assertionCount,
        private int $asyncAssertionCount,
        private ?TestFailedException $exception = null
    ) {}

    public function getTestCase() : TestCase {
        return $this->testCase;
    }

    public function getMethod() : string {
        return $this->method;
    }

    public function getAssertionCount() : int {
        return $this->assertionCount;
    }

    public function getAsyncAssertionCount() : int {
        return $this->asyncAssertionCount;
    }

    public function getFailureException() : TestFailedException|AssertionFailedException|null {
        return $this->exception;
    }

}