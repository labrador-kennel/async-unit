<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

class TestSuiteModel implements BeforeAllMethodAware, BeforeEachMethodAware, AfterEachMethodAware, AfterAllMethodAware {

    private array $testCaseModels = [];
    private array $beforeAllMethodModels = [];
    private array $beforeEachMethodModels = [];
    private array $afterEachMethodModels = [];
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

    public function addBeforeAllMethod(BeforeAllMethodModel $beforeAllMethodModel) : void {
        $this->beforeAllMethodModels[] = $beforeAllMethodModel;
    }

    public function getBeforeEachMethodModels() : array {
        return $this->beforeEachMethodModels;
    }

    public function addBeforeEachMethod(BeforeEachMethodModel $beforeEachMethodModel) : void {
        $this->beforeEachMethodModels[] = $beforeEachMethodModel;
    }

    public function getAfterEachMethodModels() : array {
        return $this->afterEachMethodModels;
    }

    public function addAfterEachMethod(AfterEachMethodModel $model) : void {
        $this->afterEachMethodModels[] = $model;
    }

    public function getAfterAllMethodModels() : array {
        return $this->afterAllMethodModels;
    }

    public function addAfterAllMethod(AfterAllMethodModel $model) : void {
        $this->afterAllMethodModels[] = $model;
    }

}