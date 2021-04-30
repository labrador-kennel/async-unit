<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Promise;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingStartedEvent;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Event\TestInvokedEvent;
use Cspray\Labrador\Plugin\Pluggable;
use stdClass;
use function Amp\call;

final class TestFrameworkApplication extends AbstractApplication {

    private EventEmitter $emitter;
    private ParserResult $parserResult;
    private TestSuiteRunner $testSuiteRunner;

    public function __construct(
        Pluggable $pluggable,
        EventEmitter $emitter,
        ParserResult $parserResult,
        TestSuiteRunner $testSuiteRunner
    ) {
        parent::__construct($pluggable);
        $this->emitter = $emitter;
        $this->parserResult = $parserResult;
        $this->testSuiteRunner = $testSuiteRunner;
    }

    protected function doStart() : Promise {
        return call(function() {

            $testRunState = new stdClass();
            $testRunState->testsInvoked = 0;
            $testRunState->failedTests = 0;
            $testRunState->totalAssertionCount = 0;
            $testRunState->totalAsyncAssertionCount = 0;

            $this->emitter->on(Events::TEST_INVOKED, function(TestInvokedEvent $testInvokedEvent) use($testRunState) {
                $testRunState->testsInvoked++;
                $testRunState->totalAssertionCount += $testInvokedEvent->getTarget()->getAssertionCount();
                $testRunState->totalAsyncAssertionCount += $testInvokedEvent->getTarget()->getAsyncAssertionCount();

                $invokedTestModel = $testInvokedEvent->getTarget();
                $testPassed = is_null($testInvokedEvent->getTarget()->getFailureException());
                $testResult = $this->getTestResult(
                    $invokedTestModel->getTestCase(),
                    $invokedTestModel->getMethod(),
                    $testInvokedEvent->getTarget()->getFailureException()
                );

                if ($testPassed) {
                    yield $this->emitter->emit(new TestPassedEvent($testResult));
                } else {
                    $testRunState->failedTests++;
                    yield $this->emitter->emit(new TestFailedEvent($testResult));
                }
            });

            yield $this->emitter->emit(
                new TestProcessingStartedEvent($this->getPreRunSummary())
            );

            yield $this->testSuiteRunner->runTestSuites(...$this->parserResult->getTestSuiteModels());

            yield $this->emitter->emit(
                new TestProcessingFinishedEvent($this->getPostRunSummary($testRunState))
            );
        });
    }

    private function getPreRunSummary() : PreRunSummary {
        return new class($this->parserResult) implements PreRunSummary {

            public function __construct(private ParserResult $parserResult) {}

            public function getTestSuiteCount() : int {
                return $this->parserResult->getTestSuiteCount();
            }

            public function getTotalTestCaseCount() : int {
                return $this->parserResult->getTotalTestCaseCount();
            }

            public function getTotalTestCount() : int {
                return $this->parserResult->getTotalTestCount();
            }
        };
    }

    private function getPostRunSummary(stdClass $testRunState) : PostRunSummary {
        return new class($testRunState) implements PostRunSummary {

            public function __construct(private stdClass $testRunState) {}

            public function getExecutedTestCount() : int {
                return $this->testRunState->testsInvoked;
            }

            public function getAssertionCount() : int {
                return $this->testRunState->totalAssertionCount;
            }

            public function getFailureTestCount() : int {
                return $this->testRunState->failedTests;
            }

            public function getAsyncAssertionCount() : int {
                return $this->testRunState->totalAsyncAssertionCount;
            }
        };
    }

    private function getTestResult(
        TestCase $testCase,
        string $method,
        ?TestFailedException $testFailedException
    ) : TestResult {
        return new class($testCase, $method, $testFailedException) implements TestResult {

            public function __construct(
                private TestCase $testCase,
                private string $method,
                private ?TestFailedException $testFailedException
            ) {}

            public function getTestCase() : TestCase {
                return $this->testCase;
            }

            public function getTestMethod() : string {
                return $this->method;
            }

            public function isSuccessful() : bool {
                return is_null($this->testFailedException);
            }

            public function getFailureException() : TestFailedException|AssertionFailedException {
                if (is_null($this->testFailedException)) {
                    throw new InvalidStateException('Attempted to access a TestFailedException on a successful TestResult.');
                }
                return $this->testFailedException;
            }
        };
    }

}