<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Internal\Model;

use Cspray\Labrador\AsyncTesting\TestCase;

class InvokedTestCaseTestModel {

    public function __construct(private TestCase $testCase, private string $method) {
    }

    public function getTestCase() : TestCase {
        return $this->testCase;
    }

    public function getMethod() : string {
        return $this->method;
    }

}