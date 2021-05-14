<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Statistics;

use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\TestResult;
use Cspray\Labrador\AsyncUnit\TestState;
use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\Timer;

/**
 * @package Cspray\Labrador\AsyncUnit\Statistics
 * @internal
 */
final class ProcessedAggregateSummaryBuilder {

    private array $testSuites = [];
    private int $disabledTestSuiteCount = 0;
    private int $totalTestCaseCount = 0;
    private int $disabledTestCaseCount = 0;
    private int $totalTestCount = 0;
    private int $disabledTestCount = 0;
    private int $passedTestCount = 0;
    private int $failedTestCount = 0;
    private int $assertionCount = 0;
    private int $asyncAssertionCount = 0;

    private Timer $timer;
    private Duration $duration;
    private int $memoryUsageInBytes;

    public function startProcessing() : void {
        $this->timer = new Timer();
        $this->timer->start();
    }

    public function processedTestSuite(TestSuiteModel $testSuiteModel) : void {
        $this->testSuites[] = $testSuiteModel->getClass();
        if ($testSuiteModel->isDisabled()) {
            $this->disabledTestSuiteCount++;
        }
    }

    public function processedTestCase(TestCaseModel $testCaseModel) : void {
        $this->totalTestCaseCount++;
        if ($testCaseModel->isDisabled()) {
            $this->disabledTestCaseCount++;
        }
    }

    public function processedTest(TestResult $testResult) : void {
        $this->totalTestCount++;
        $this->assertionCount += $testResult->getTestCase()->getAssertionCount();
        $this->asyncAssertionCount += $testResult->getTestCase()->getAsyncAssertionCount();
        if (TestState::Disabled()->equals($testResult->getState())) {
            $this->disabledTestCount++;
        } else if (TestState::Passed()->equals($testResult->getState())) {
            $this->passedTestCount++;
        } else if (TestState::Failed()->equals($testResult->getState())) {
            $this->failedTestCount++;
        }
    }

    public function finishProcessing() : void {
        $this->duration = $this->timer->stop();
        $this->memoryUsageInBytes = memory_get_peak_usage(true);
    }

    public function build() : ProcessedAggregateSummary {
        return new class(
            $this->testSuites,
            count($this->testSuites),
            $this->disabledTestSuiteCount,
            $this->totalTestCaseCount,
            $this->disabledTestCaseCount,
            $this->totalTestCount,
            $this->disabledTestCount,
            $this->passedTestCount,
            $this->failedTestCount,
            $this->assertionCount,
            $this->asyncAssertionCount,
            $this->duration,
            $this->memoryUsageInBytes
        ) implements ProcessedAggregateSummary {

            public function __construct(
                private array $testSuiteNames,
                private int $totalTestSuiteCount,
                private int $disabledTestSuiteCount,
                private int $totalTestCaseCount,
                private int $disabledTestCaseCount,
                private int $totalTestCount,
                private int $disabledTestCount,
                private int $passedTestCount,
                private int $failedTestCount,
                private int $assertionCount,
                private int $asyncAssertionCount,
                private Duration $duration,
                private int $memoryUsageInBytes
            ) {}

            public function getTestSuiteNames() : array {
                return $this->testSuiteNames;
            }

            public function getTotalTestSuiteCount() : int {
                return $this->totalTestSuiteCount;
            }

            public function getEnabledTestSuiteCount() : int {
                return $this->getTotalTestSuiteCount() - $this->getDisabledTestSuiteCount();
            }

            public function getDisabledTestSuiteCount() : int {
                return $this->disabledTestSuiteCount;
            }

            public function getTotalTestCaseCount() : int {
                return $this->totalTestCaseCount;
            }

            public function getEnabledTestCaseCount() : int {
                return $this->getTotalTestCaseCount() - $this->getDisabledTestCaseCount();
            }

            public function getDisabledTestCaseCount() : int {
                return $this->disabledTestCaseCount;
            }

            public function getTotalTestCount() : int {
                return $this->totalTestCount;
            }

            public function getEnabledTestCount() : int {
                return $this->getTotalTestCount() - $this->getDisabledTestCount();
            }

            public function getDisabledTestCount() : int {
                return $this->disabledTestCount;
            }

            public function getPassedTestCount() : int {
                return $this->passedTestCount;
            }

            public function getFailedTestCount() : int {
                return $this->failedTestCount;
            }

            public function getDuration() : Duration {
                return $this->duration;
            }

            public function getMemoryUsageInBytes() : int {
                return $this->memoryUsageInBytes;
            }

            public function getAssertionCount() : int {
                return $this->assertionCount;
            }

            public function getAsyncAssertionCount() : int {
                return $this->asyncAssertionCount;
            }
        };
    }

}