<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Internal\Model;

use Cspray\Labrador\AsyncTesting\Exception\TestFailedException;
use Cspray\Labrador\AsyncTesting\TestCase;

/**
 * @internal
 */
class InvokedTestCaseTestModel {

    public function __construct(private TestCase $testCase, private string $method, private ?TestFailedException $exception = null) {
    }

    public function getTestCase() : TestCase {
        return $this->testCase;
    }

    public function getMethod() : string {
        return $this->method;
    }

    public function getFailureException() : ?TestFailedException {
        return $this->exception;
    }

}