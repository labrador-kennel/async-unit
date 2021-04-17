<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Internal;

use Amp\Promise;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncTesting\AssertionContext;
use Cspray\Labrador\AsyncTesting\AssertionContextFacade;
use Cspray\Labrador\AsyncTesting\AsyncAssertionContext;
use Cspray\Labrador\AsyncTesting\Internal\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncTesting\Internal\Model\InvokedTestCaseTestModel;
use Cspray\Labrador\AsyncTesting\Internal\Model\TestSuiteModel;
use ReflectionClass;
use function Amp\call;

class TestSuiteRunner {

    private array $reflectionCache = [];

    public function __construct(private EventEmitter $emitter) {}

    public function runTestSuites(TestSuiteModel... $testSuiteModels) : Promise {
        return call(function() use($testSuiteModels) {
            foreach ($testSuiteModels as $testSuiteModel) {
                foreach ($testSuiteModel->getTestCaseModels() as $testCaseModel) {
                    $reflectionClass = $this->getReflectionClass($testCaseModel->getTestCaseClass());
                    foreach ($testCaseModel->getBeforeAllMethodModels() as $beforeAllMethodModel) {
                        $reflectionMethod = $reflectionClass->getMethod($beforeAllMethodModel->getMethod());
                        yield call(fn() => $reflectionMethod->invoke(null));
                    }

                    foreach ($testCaseModel->getTestMethodModels() as $testMethodModel) {
                        $testCaseObject = $reflectionClass->newInstanceWithoutConstructor();
                        $testCaseConstructor = $reflectionClass->getConstructor();
                        $testCaseConstructor->setAccessible(true);

                        $reflectedAssertionContext = $this->getReflectionClass(AssertionContext::class);
                        $reflectedAsyncAssertionContext = $this->getReflectionClass(AsyncAssertionContext::class);

                        $assertionContextFacade = new AssertionContextFacade(
                            $reflectedAssertionContext->newInstanceWithoutConstructor(),
                            $reflectedAsyncAssertionContext->newInstanceWithoutConstructor()
                        );

                        $testCaseConstructor->invoke($testCaseObject, $assertionContextFacade);
                        foreach ($testCaseModel->getBeforeEachMethodModels() as $beforeEachMethodModel) {
                            $reflectionMethod = $reflectionClass->getMethod($beforeEachMethodModel->getMethod());
                            yield call(fn() => $reflectionMethod->invoke($testCaseObject));
                        }

                        $testCaseMethod = $reflectionClass->getMethod($testMethodModel->getMethod());
                        yield call(fn() => $testCaseMethod->invoke($testCaseObject));
                        $invokedModel = new InvokedTestCaseTestModel($testCaseObject, $testMethodModel->getMethod());

                        foreach ($testCaseModel->getAfterEachMethodModels() as $afterEachMethodModel) {
                            $reflectionMethod = $reflectionClass->getMethod($afterEachMethodModel->getMethod());
                            yield call(fn() => $reflectionMethod->invoke($testCaseObject));
                        }
                        yield $this->emitter->emit(new TestInvokedEvent($invokedModel));
                    }

                    foreach ($testCaseModel->getAfterAllMethodModels() as $afterAllMethodModel) {
                        $reflectionMethod = $reflectionClass->getMethod($afterAllMethodModel->getMethod());
                        yield call(fn() => $reflectionMethod->invoke(null));
                    }
                }
            }
        });
        // yield the instantiated object and name of invoked method
    }

    private function getReflectionClass(string $class) : ReflectionClass {
        if (!isset($this->reflectionCache[$class])) {
            $this->reflectionCache[$class] = new ReflectionClass($class);
        }

        return $this->reflectionCache[$class];
    }

    // TestCaseInfo == parsed details of a TestCase ... includes which methods are marked as before/after each/all and
    // which methods are marked as tests

    // TestSuiteInfo == parsed details of an _explicit_ TestSuite ... includes which TestCase classes are part of this
    // test suite and which methods are marked as before each/all

    // MethodHook == Details of a TestSuite or TestCase hook that can be invoked to actually execute the thing (???) *Need name that also works for test methods*

    // parse files into list of TestSuiteInfo and TestCaseInfo data

    // if there are NO TestCaseInfo there are no tests - LOGICAL ERROR

    // Create list empty TestSuite for each explicit TestSuite + an empty default TestSuite
    // Create a map for TestSuite method hooks...
    //      [
    //          TestSuite => [
    //              'beforeAll' => [],
    //              'beforeEach' => [],
    //              'afterAll' => [],
    //              'beforeAll' => []
    //          ],
    //          ...
    //      ]

    // for each explicit TestSuiteInfo
        // Determine if any methods are marked as hooks. If there are add MethodHook to TestSuite method hooks map
            // For each found hook validate that the method signature matches what we expect. If invalid - LOGICAL ERROR

    // Create a map for TestCase method hooks ...
    //      [
    //          TestCase => [
    //              'beforeAll' => [],
    //              'beforeEach' => [],
    //              'afterAll' => [],
    //              'afterEach' => [],
    //              'tests' => []
    //      ],
    //      ...

    // For each TestCaseInfo
        // Determine if the corresponding TestCase is marked for an explicit TestSuite (need to figure out best way to do this)
        // If explicit TestSuite check that it exists and we're aware of it. If not aware of it error reached - LOGICAL ERROR
        // Add TestCase class to either explicit or default TestSuite

        // Determine if any methods are marked as hooks. If there are any add MethodHooks to TestCase map
            // For each found hook validate that the method signature matches what we expect. If invalid - LOGICAL ERROR
        // Determine if any methods are marked as tests. If there are any add MethodHooks to TestCase map. If no methods are marked as tests no tests for this TestCase - LOGICAL ERROR
            // For each found method validate that the method signature matches what we expect. If invalid - LOGICAL ERROR

    // Start the Loop ... everything from here out needs to be async aware
        // For each TestSuite
            // Instantiate the TestSuite object
            // Determine if there are any beforeAll MethodHooks and call them

            // Emit an event that the TestSuite has started

            // For each TestCase in TestSuite
                // Emit an event that the TestCase has started

                // Determine if there are any TestCase beforeAll MethodHooks and call them - Should be static methods on the TestCase class
                // For each tests in TestCase MethodHook map
                    // Instantiate a fresh TestCase object
                    // Determine if there are any TestSuite beforeEach MethodHooks and call them
                    // Determine if there are any TestCase beforeEach MethodHooks and call them
                    // Invoke the test
                        // Emit an event that the Test has failed or succeeded
                        // Still to figure out how to best deal with test results and assertions. Assume something that works similar to PHPUnit assertions exception more async support
                    // Determine if there are any TestCase afterEach MethodHooks and call them
                    // Determine if there are any TestSuite afterEach MethodHooks and call them

                    // Destroy the TestCase object

                // Emit an event that the TestCase has ended - provide overall information on successes/failures
            // Emit an event that the TestSuite has ended - provide overall information on successes/failures

}