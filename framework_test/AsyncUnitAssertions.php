<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncUnit\Constraint\TestCaseModelHasTestMethod;
use Cspray\Labrador\AsyncUnit\Constraint\TestSuiteModelHasTestCaseModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestSuiteModel;

trait AsyncUnitAssertions {

    public function assertTestCaseClassBelongsToTestSuite(string $expected, TestSuiteModel $actual) {
        $constraint = new TestSuiteModelHasTestCaseModel($expected);
        $this->assertThat($actual, $constraint);
    }

    public function assertTestMethodBelongsToTestCase(string $methodSignature, TestCaseModel $actual) {
        [$testClass, $method] = explode('::', $methodSignature);
        $constraint = new TestCaseModelHasTestMethod($testClass, $method);
        $this->assertThat($actual, $constraint);
    }

}