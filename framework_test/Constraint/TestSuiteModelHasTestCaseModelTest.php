<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Constraint;

use Cspray\Labrador\AsyncUnit\ImplicitTestSuite;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class TestSuiteModelHasTestCaseModelTest extends TestCase {

    public function testPassingNonTestSuiteThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'You must pass a %s to %s',
            TestSuiteModel::class,
            TestSuiteModelHasTestCaseModel::class
        ));

        (new TestSuiteModelHasTestCaseModel(''))->evaluate(new stdClass(), returnResult: true);
    }

    public function testPassingEmptyTestSuiteFails() {
        $testSuite = new TestSuiteModel(ImplicitTestSuite::class, true);
        $results = (new TestSuiteModelHasTestCaseModel(''))->evaluate($testSuite, returnResult: true);
        $this->assertFalse($results);
    }

    public function testTestSuiteHasTestCaseClassPasses() {
        $testSuite = new TestSuiteModel(ImplicitTestSuite::class, true);
        $testCaseModel = new TestCaseModel('TestCaseClass');
        $testSuite->addTestCaseModel($testCaseModel);

        $results = (new TestSuiteModelHasTestCaseModel('TestCaseClass'))->evaluate($testSuite, returnResult: true);
        $this->assertTrue($results);
    }

    public function testTestSuiteDoesNotHaveTestCaseClassFails() {
        $testSuite = new TestSuiteModel(ImplicitTestSuite::class, true);
        $testCaseModel = new TestCaseModel('FooClass');
        $testSuite->addTestCaseModel($testCaseModel);

        $results = (new TestSuiteModelHasTestCaseModel('BarClass'))->evaluate($testSuite, returnResult: true);
        $this->assertFalse($results);
    }

}