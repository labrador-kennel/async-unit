<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Parser;

use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\Model\HookModel;
use Cspray\Labrador\AsyncUnit\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;

/**
 * @internal
 */
final class AsyncUnitModelCollector {

    private ?string $defaultTestSuite = null;

    /**
     * @var TestSuiteModel[]
     */
    private array $testSuiteModels = [];

    /**
     * @var TestCaseModel[]
     */
    private array $testCaseModels = [];

    /**
     * @var TestModel[]
     */
    private array $testModels = [];

    /**
     * @var HookModel[]
     */
    private array $hookModels = [];

    /**
     * @var PluginModel[]
     */
    private array $pluginModels= [];

    public function attachTestSuite(TestSuiteModel $testSuiteModel) : void {
        if ($testSuiteModel->isDefaultTestSuite()) {
            $this->defaultTestSuite = $testSuiteModel->getClass();
        }
        $this->testSuiteModels[$testSuiteModel->getClass()] = $testSuiteModel;
    }

    public function attachTestCase(TestCaseModel $testCaseModel) : void {
        $this->testCaseModels[] = $testCaseModel;
    }

    public function attachTest(TestModel $testModel) : void {
        $this->testModels[] = $testModel;
    }

    public function attachHook(HookModel $hookModel) : void {
        $this->hookModels[] = $hookModel;
    }

    public function attachPlugin(PluginModel $pluginModel) : void {
        $this->pluginModels[] = $pluginModel;
    }

    public function hasDefaultTestSuite() : bool {
        return isset($this->defaultTestSuite);
    }

    public function getTestSuiteModels() : array {
        return array_values($this->testSuiteModels);
    }

    public function getPluginModels() : array {
        return $this->pluginModels;
    }

    public function finishedCollection() : void {
        foreach ($this->hookModels as $hookModel) {
            foreach (array_merge([], $this->testSuiteModels, $this->testCaseModels) as $model) {
                if ($hookModel->getClass() === $model->getClass()) {
                    $model->addHook($hookModel);
                    continue 2;
                }
            }
        }

        foreach ($this->testCaseModels as $testCaseModel) {
            if (is_null($testCaseModel->getTestSuiteClass())) {
                $testCaseModel->setTestSuiteClass($this->defaultTestSuite);
            }

            $testSuiteModel = $this->testSuiteModels[$testCaseModel->getTestSuiteClass()];
            if ($testSuiteModel->isDisabled()) {
                $testCaseModel->markDisabled($testSuiteModel->getDisabledReason());
            }
            if (!is_null($testSuiteModel->getTimeout())) {
                $testCaseModel->setTimeout($testSuiteModel->getTimeout());
            }
            $testSuiteModel->addTestCaseModel($testCaseModel);
            foreach ($this->testModels as $testModel) {
                $testClass = $testModel->getClass();
                if ($testCaseModel->getClass() === $testClass || is_subclass_of($testCaseModel->getClass(), $testClass)) {
                    $testCaseTest = $testModel->withClass($testCaseModel->getClass());
                    if ($testCaseModel->isDisabled()) {
                        $testCaseTest->markDisabled($testCaseModel->getDisabledReason());
                    }
                    $testCaseTimeout = $testCaseModel->getTimeout();
                    if (!is_null($testCaseTimeout)) {
                        $testCaseTest->setTimeout($testCaseTimeout);
                    }
                    $testCaseModel->addTestModel($testCaseTest);
                }
            }
            if (empty($testCaseModel->getTestModels())) {
                $msg = sprintf(
                    'Failure compiling "%s". There were no #[Test] found.',
                    $testCaseModel->getClass()
                );
                throw new TestCompilationException($msg);
            }
        }

        if (empty($this->testSuiteModels[$this->defaultTestSuite]->getTestCaseModels())) {
            unset($this->testSuiteModels[$this->defaultTestSuite]);
        }

        unset($this->testModels);
    }

}