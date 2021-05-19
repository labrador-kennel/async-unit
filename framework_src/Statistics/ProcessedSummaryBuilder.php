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
final class ProcessedSummaryBuilder {

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

    public function startTestSuite(TestSuiteModel $testSuiteModel) : void {
        $timer = new Timer();
        $timer->start();
        $this->testSuites[$testSuiteModel->getClass()] = [
            'enabled' => [],
            'disabled' => [],
            'timer' => $timer
        ];
        if ($testSuiteModel->isDisabled()) {
            $this->disabledTestSuiteCount++;
        }
    }

    public function finishTestSuite(TestSuiteModel $testSuiteModel) : ProcessedTestSuiteSummary {
        $duration = $this->testSuites[$testSuiteModel->getClass()]['timer']->stop();
        $this->testSuites[$testSuiteModel->getClass()]['duration'] = $duration;
        return $this->buildTestSuiteSummary($testSuiteModel);
    }

    public function startTestCase(TestCaseModel $testCaseModel) : void {
        $key = $testCaseModel->isDisabled() ? 'disabled' : 'enabled';
        $this->testSuites[$testCaseModel->getTestSuiteClass()][$key][$testCaseModel->getClass()] = [
            TestState::Passed()->toString() => [],
            TestState::Failed()->toString() => [],
            TestState::Disabled()->toString() => []
        ];
        $this->totalTestCaseCount++;
        if ($testCaseModel->isDisabled()) {
            $this->disabledTestCaseCount++;
        }
    }

    public function processedTestCase(TestCaseModel $testCaseModel) : void {
    }

    public function processedTest(TestResult $testResult) : void {
        $testSuiteClass = $testResult->getTestCase()->testSuite()::class;
        $testCaseClass = $testResult->getTestCase()::class;
        $key =  isset($this->testSuites[$testSuiteClass]['enabled'][$testCaseClass]) ? 'enabled' : 'disabled';
        $stateKey = $testResult->getState()->toString();

        $testName = sprintf('%s::%s', $testCaseClass, $testResult->getTestMethod());
        $this->testSuites[$testSuiteClass][$key][$testCaseClass][$stateKey][$testName] = [
            'assertion' => $testResult->getTestCase()->getAssertionCount(),
            'asyncAssertion' => $testResult->getTestCase()->getAsyncAssertionCount()
        ];

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

    public function finishProcessing() : ProcessedAggregateSummary {
        $this->duration = $this->timer->stop();
        $this->memoryUsageInBytes = memory_get_peak_usage(true);
        return $this->buildAggregate();
    }

    private function buildAggregate() : ProcessedAggregateSummary {
        $testSuiteNames = array_keys($this->testSuites);
        return new class(
            $testSuiteNames,
            count($testSuiteNames),
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

            public function getDisabledTestSuiteCount() : int {
                return $this->disabledTestSuiteCount;
            }

            public function getTotalTestCaseCount() : int {
                return $this->totalTestCaseCount;
            }

            public function getDisabledTestCaseCount() : int {
                return $this->disabledTestCaseCount;
            }

            public function getTotalTestCount() : int {
                return $this->totalTestCount;
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

    private function buildTestSuiteSummary(TestSuiteModel $testSuiteModel) : ProcessedTestSuiteSummary {
        $testSuiteName = $testSuiteModel->getClass();
        $enabledTestCases = array_keys($this->testSuites[$testSuiteName]['enabled']);
        $disabledTestCases = array_keys($this->testSuites[$testSuiteName]['disabled']);
        $testCaseNames = array_merge([], $enabledTestCases, $disabledTestCases);
        $disabledTestCount = 0;
        $passedTestCount = 0;
        $failedTestCount = 0;
        $assertionCount = 0;
        $asyncAssertionCount = 0;
        foreach ($enabledTestCases as $testCase) {
            $tests = $this->testSuites[$testSuiteName]['enabled'][$testCase];
            $passedTestCount += count($tests[TestState::Passed()->toString()]);
            $failedTestCount += count($tests[TestState::Failed()->toString()]);
            $disabledTestCount += count($tests[TestState::Disabled()->toString()]);
            foreach ($tests[TestState::Passed()->toString()] as $assertionCounts) {
                $assertionCount += $assertionCounts['assertion'];
                $asyncAssertionCount += $assertionCounts['asyncAssertion'];
            }
            foreach ($tests[TestState::Failed()->toString()] as $assertionCounts) {
                $assertionCount += $assertionCounts['assertion'];
                $asyncAssertionCount += $assertionCounts['asyncAssertion'];
            }
        }

        foreach ($disabledTestCases as $testCase) {
            $tests = $this->testSuites[$testSuiteName]['disabled'][$testCase];
            $disabledTestCount += count($tests[TestState::Disabled()->toString()]);
            $passedDisabledTestCount = count($tests[TestState::Passed()->toString()]);
            $failedDisabledTestCount = count($tests[TestState::Failed()->toString()]);

            // TODO make sure this logs a warning when we implement our logger
            assert($passedDisabledTestCount === 0, 'A disabled TestCase had passed tests associated to it.');
            assert($failedDisabledTestCount === 0, 'A disabled TestCase had failed tests associated to it.');
        }

        $totalTestCount = $disabledTestCount + $passedTestCount + $failedTestCount;

        return new class(
            $testSuiteName,
            $testCaseNames,
            count($testCaseNames),
            count($disabledTestCases),
            $totalTestCount,
            $disabledTestCount,
            $passedTestCount,
            $failedTestCount,
            $assertionCount,
            $asyncAssertionCount,
            $this->testSuites[$testSuiteName]['duration']
        ) implements ProcessedTestSuiteSummary {

            public function __construct(
                private string $testSuiteName,
                private array $testCaseNames,
                private int $totalTestCaseCount,
                private int $disabledTestCaseCount,
                private int $totalTestCount,
                private int $disabledTestCount,
                private int $passedTestCount,
                private int $failedTestCount,
                private int $assertionCount,
                private int $asyncAssertionCount,
                private Duration $duration
            ) {}

            public function getTestSuiteName() : string {
                return $this->testSuiteName;
            }

            public function getTestCaseNames() : array {
                return $this->testCaseNames;
            }

            public function getTestCaseCount() : int {
                return $this->totalTestCaseCount;
            }

            public function getDisabledTestCaseCount() : int {
                return $this->disabledTestCaseCount;
            }

            public function getTestCount() : int {
                return $this->totalTestCount;
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

            public function getAssertionCount() : int {
                return $this->assertionCount;
            }

            public function getAsyncAssertionCount() : int {
                return $this->asyncAssertionCount;
            }

            public function getDuration() : Duration {
                return $this->duration;
            }
        };
    }
}