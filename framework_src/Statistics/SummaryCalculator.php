<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Statistics;

use Cspray\Labrador\AsyncUnit\Parser\ParserResult;

/**
 * @package Cspray\Labrador\AsyncUnit\Statistics
 * @internal
 */
final class SummaryCalculator {

    private array $modelRelationships = [];
    private AggregateSummary $aggregateSummary;

    private int $testSuiteCount = 0;

    private int $testCaseCount = 0;

    private int $testCount = 0;

    public function __construct(private ParserResult $parserResult) {
        $this->calculateModelRelationships($this->parserResult);
    }

    private function calculateModelRelationships(ParserResult $parserResult) {
        foreach ($parserResult->getTestSuiteModels() as $testSuiteModel) {
            $testSuite = $testSuiteModel->getClass();
            $this->testSuiteCount++;
            if (!isset($this->modelRelationships[$testSuite])) {
                $this->modelRelationships[$testSuite] = [];
            }

            foreach ($testSuiteModel->getTestCaseModels() as $testCaseModel) {
                $testCase = $testCaseModel->getClass();
                $this->testCaseCount++;
                if (!isset($this->modelRelationships[$testSuite][$testCase])) {
                    $this->modelRelationships[$testSuite][$testCase] = [];
                }

                foreach ($testCaseModel->getTestModels() as $testModel) {
                    $this->testCount++;
                    $testName = sprintf(
                        '%s::%s',
                        $testModel->getClass(),
                        $testModel->getMethod()
                    );
                    $this->modelRelationships[$testSuite][$testCase][] = $testName;
                }
            }

        }
    }

    public function getAggregateSummary() : AggregateSummary {
        if (!isset($this->aggregateSummary)) {
            $this->aggregateSummary = $this->constructAggregateSummary();
        }

        return $this->aggregateSummary;
    }

    private function constructAggregateSummary() : AggregateSummary {
        $testSuiteNames = array_keys($this->modelRelationships);
        return new class(
            $testSuiteNames,
            $this->testSuiteCount,
            $this->testCaseCount,
            $this->testCount,
        ) implements AggregateSummary {

            public function __construct(
                private array $testSuiteNames,
                private int $totalTestSuiteCount,
                private int $totalTestCaseCount,
                private int $totalTestCount,
            ) {}

            public function getTestSuiteNames() : array {
                return $this->testSuiteNames;
            }

            public function getTotalTestSuiteCount() : int {
                return $this->totalTestSuiteCount;
            }

            public function getTotalTestCaseCount() : int {
                return $this->totalTestCaseCount;
            }

            public function getTotalTestCount() : int {
                return $this->totalTestCount;
            }
        };
    }

    public function getTestSuiteSummary(string $testSuite) : TestSuiteSummary {
        $testCaseNames = array_keys($this->modelRelationships[$testSuite]);
        $testCaseCount = count($testCaseNames);
        $suiteTests = [];
        foreach ($this->modelRelationships[$testSuite] as $tests) {
            foreach ($tests as $test) {
                $suiteTests[] = $test;
            }
        }
        return new class(
            $testSuite,
            $testCaseNames,
            $testCaseCount,
            count($suiteTests)
        ) implements TestSuiteSummary {

            public function __construct(
                private string $testSuiteName,
                private array $testCaseNames,
                private int $testCaseCount,
                private int $testCount
            ) {}

            public function getTestSuiteName() : string {
                return $this->testSuiteName;
            }

            public function getTestCaseNames() : array {
                return $this->testCaseNames;
            }

            public function getTestCaseCount() : int {
                return $this->testCaseCount;
            }

            public function getTestCount() : int {
                return $this->testCount;
            }
        };
    }

    public function getTestCaseSummary(string $testCase) : TestCaseSummary {
        $testSuite = null;
        $tests = null;
        foreach ($this->modelRelationships as $_testSuite => $testCases) {
            if (array_key_exists($testCase, $testCases)) {
                $testSuite = $_testSuite;
                $tests = $testCases[$testCase];
                break;
            }
        }

        return new class(
            $testSuite,
            $testCase,
            $tests,
            count($tests)
        ) implements TestCaseSummary {

            public function __construct(
                private string $testSuiteName,
                private string $testCaseName,
                private array $testNames,
                private int $testCount
            ) {}

            public function getTestSuiteName() : string {
                return $this->testSuiteName;
            }

            public function getTestCaseName() : string {
                return $this->testCaseName;
            }

            public function getTestNames() : array {
                return $this->testNames;
            }

            public function getTestCount() : int {
                return $this->testCount;
            }
        };
    }
}