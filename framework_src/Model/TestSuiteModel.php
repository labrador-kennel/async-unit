<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

final class TestSuiteModel implements BeforeAllMethodAware, BeforeEachMethodAware, AfterEachMethodAware, AfterAllMethodAware {

    private array $testCaseModels = [];
    private array $beforeAllMethodModels = [];
    private array $beforeEachMethodModels = [];
    private array $beforeEachTestMethodModels = [];
    private array $afterEachMethodModels = [];
    private array $afterEachTestMethodModels = [];
    private array $afterAllMethodModels = [];

    public function __construct(
        private string $class,
        private bool $isDefaultTestSuite
    ) {}

    public function getClass() : string {
        return $this->class;
    }

    public function isDefaultTestSuite() : bool {
        return $this->isDefaultTestSuite;
    }

    /**
     * @return TestCaseModel[]
     */
    public function getTestCaseModels() : array {
        return $this->testCaseModels;
    }

    public function addTestCaseModel(TestCaseModel $testCaseModel) : void {
        $this->testCaseModels[] = $testCaseModel;
    }

    public function getBeforeAllMethodModels() : array {
        return $this->beforeAllMethodModels;
    }

    public function addBeforeAllMethod(HookMethodModel $model) : void {
        $this->beforeAllMethodModels[] = $model;
    }

    public function getBeforeEachMethodModels() : array {
        return $this->beforeEachMethodModels;
    }

    public function addBeforeEachMethod(HookMethodModel $model) : void {
        $this->beforeEachMethodModels[] = $model;
    }

    public function getBeforeEachTestMethodModels() : array {
        return $this->beforeEachTestMethodModels;
    }

    public function addBeforeEachTestMethod(HookMethodModel $model) : void {
        $this->beforeEachTestMethodModels[] = $model;
    }

    public function getAfterEachMethodModels() : array {
        return $this->afterEachMethodModels;
    }

    public function addAfterEachMethod(HookMethodModel $model) : void {
        $this->afterEachMethodModels[] = $model;
    }

    public function getAfterEachTestMethodModels() : array {
        return $this->afterEachTestMethodModels;
    }

    public function addAfterEachTestMethod(HookMethodModel $model) : void {
        $this->afterEachTestMethodModels[] = $model;
    }

    public function getAfterAllMethodModels() : array {
        return $this->afterAllMethodModels;
    }

    public function addAfterAllMethod(HookMethodModel $model) : void {
        $this->afterAllMethodModels[] = $model;
    }

}