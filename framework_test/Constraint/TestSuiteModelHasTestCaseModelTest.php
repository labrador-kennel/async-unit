<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Constraint;

use Cspray\Labrador\AsyncUnit\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Cspray\Labrador\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Constraint\TestSuiteModelHasTestCaseModel
 */
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
        $testSuite = new TestSuiteModel();
        $results = (new TestSuiteModelHasTestCaseModel(''))->evaluate($testSuite, returnResult: true);
        $this->assertFalse($results);
    }

    public function testTestSuiteHasTestCaseClassPasses() {
        $testSuite = new TestSuiteModel();
        $testCaseModel = new TestCaseModel('TestCaseClass');
        $testSuite->addTestCaseModel($testCaseModel);

        $results = (new TestSuiteModelHasTestCaseModel('TestCaseClass'))->evaluate($testSuite, returnResult: true);
        $this->assertTrue($results);
    }

    public function testTestSuiteDoesNotHaveTestCaseClassFails() {
        $testSuite = new TestSuiteModel();
        $testCaseModel = new TestCaseModel('FooClass');
        $testSuite->addTestCaseModel($testCaseModel);

        $results = (new TestSuiteModelHasTestCaseModel('BarClass'))->evaluate($testSuite, returnResult: true);
        $this->assertFalse($results);
    }

}