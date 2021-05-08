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
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Cspray\Labrador\Plugin\Pluggable;
use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\Timer;
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
            $testRunState->testsProcessed = 0;
            $testRunState->failedTests = 0;
            $testRunState->disabledTests = 0;
            $testRunState->totalAssertionCount = 0;
            $testRunState->totalAsyncAssertionCount = 0;

            $this->emitter->on(Events::TEST_PROCESSED, function(TestProcessedEvent $testInvokedEvent) use($testRunState) {
                $testRunState->testsProcessed++;
                $testRunState->totalAssertionCount += $testInvokedEvent->getTarget()->getTestCase()->getAssertionCount();
                $testRunState->totalAsyncAssertionCount += $testInvokedEvent->getTarget()->getTestCase()->getAsyncAssertionCount();
                if (TestState::Failed()->equals($testInvokedEvent->getTarget()->getState())) {
                    $testRunState->failedTests++;
                } else if (TestState::Disabled()->equals($testInvokedEvent->getTarget()->getState())) {
                    $testRunState->disabledTests++;
                }
            });

            yield $this->emitter->emit(
                new TestProcessingStartedEvent($this->getPreRunSummary())
            );

            $timer = new Timer();
            $timer->start();

            yield $this->testSuiteRunner->runTestSuites(...$this->parserResult->getTestSuiteModels());

            $testRunState->duration = $timer->stop();
            $testRunState->memoryUsage = memory_get_peak_usage(true);

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

            public function getAssertionCount() : int {
                return $this->testRunState->totalAssertionCount;
            }

            public function getAsyncAssertionCount() : int {
                return $this->testRunState->totalAsyncAssertionCount;
            }

            public function getTotalTestCount() : int {
                return $this->testRunState->testsProcessed;
            }

            public function getPassedTestCount() : int {
                return $this->getTotalTestCount() - $this->getFailedTestCount() - $this->getDisabledTestCount();
            }

            public function getFailedTestCount() : int {
                return $this->testRunState->failedTests;
            }

            public function getDisabledTestCount() : int {
                return $this->testRunState->disabledTests;
            }

            public function getMemoryUsageInBytes() : int {
                return $this->testRunState->memoryUsage;
            }

            public function getDuration() : Duration {
                return $this->testRunState->duration;
            }
        };
    }

}