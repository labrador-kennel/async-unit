<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Parser;

use Cspray\Labrador\AsyncUnit\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\Statistics\AggregateSummary;
use Cspray\Labrador\AsyncUnit\Statistics\SummaryCalculator;
use Cspray\Labrador\AsyncUnit\Statistics\TestCaseSummary;
use Cspray\Labrador\AsyncUnit\Statistics\TestSuiteSummary;

final class ParserResult {

    private SummaryCalculator $summaryCalculator;

    public function __construct(private AsyncUnitModelCollector $collector) {}

    /**
     * @return TestSuiteModel[]
     */
    public function getTestSuiteModels() : array {
        return $this->collector->getTestSuiteModels();
    }

    /**
     * @return PluginModel[]
     */
    public function getPluginModels() : array {
        return $this->collector->getPluginModels();
    }

    public function getAggregateSummary() : AggregateSummary {
        return $this->getSummaryCalculator()->getAggregateSummary();
    }

    public function getTestSuiteSummary(string $testSuite) : TestSuiteSummary {
        return $this->getSummaryCalculator()->getTestSuiteSummary($testSuite);
    }

    public function getTestCaseSummary(string $testCase) : TestCaseSummary {
        return $this->getSummaryCalculator()->getTestCaseSummary($testCase);
    }

    private function getSummaryCalculator() : SummaryCalculator {
        if (!isset($this->summaryCalculator)) {
            $this->summaryCalculator = new SummaryCalculator($this);
        }
        return $this->summaryCalculator;
    }

}