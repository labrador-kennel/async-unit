<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncUnit\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;

final class ParserResult {

    public function __construct(
        private array $testSuiteModels,
        private array $pluginModels,
        private int $totalTestCaseCount,
        private int $totalTestCount
    ) {}

    public function getTestSuiteCount() : int {
        return count($this->testSuiteModels);
    }

    public function getTotalTestCaseCount() : int {
        return $this->totalTestCaseCount;
    }

    public function getTotalTestCount() : int {
        return $this->totalTestCount;
    }

    /**
     * @return TestSuiteModel[]
     */
    public function getTestSuiteModels() : array {
        return $this->testSuiteModels;
    }

    /**
     * @return PluginModel[]
     */
    public function getPluginModels() : array {
        return $this->pluginModels;
    }

}