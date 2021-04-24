<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal;

use Cspray\Labrador\AsyncUnit\Internal\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestSuiteModel;

/**
 * @internal
 */
class ParserResult {


    public function __construct(
        private array $testSuiteModels,
        private array $pluginModels
    ) {}

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