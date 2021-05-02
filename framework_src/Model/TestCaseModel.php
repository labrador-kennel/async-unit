<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

final class TestCaseModel implements BeforeAllMethodAware, BeforeEachMethodAware, AfterEachMethodAware, AfterAllMethodAware {

    private array $testMethodModels = [];
    private array $beforeAllMethodModels = [];
    private array $beforeEachMethodModels = [];
    private array $afterAllMethodModels = [];
    private array $afterEachMethodModels = [];

    public function __construct(
        private string $testCaseClass,
        private ?string $testSuiteClass = null
    ) {}

    public function getTestSuiteClass() : ?string {
        return $this->testSuiteClass;
    }

    public function getClass() : string {
        return $this->testCaseClass;
    }

    /**
     * @return TestMethodModel[]
     */
    public function getTestMethodModels() : array {
        return $this->testMethodModels;
    }

    /**
     * @return BeforeAllMethodModel[]
     */
    public function getBeforeAllMethodModels() : array {
        return $this->beforeAllMethodModels;
    }

    /**
     * @return BeforeEachMethodModel[]
     */
    public function getBeforeEachMethodModels() : array {
        return $this->beforeEachMethodModels;
    }

    /**
     * @return AfterAllMethodModel[]
     */
    public function getAfterAllMethodModels() : array {
        return $this->afterAllMethodModels;
    }

    public function getAfterEachMethodModels() : array {
        return $this->afterEachMethodModels;
    }

    public function addTestMethodModel(TestMethodModel $testMethodModel) : void {
        $this->testMethodModels[] = $testMethodModel;
    }

    public function addBeforeAllMethod(HookMethodModel $model) : void {
        $this->beforeAllMethodModels[] = $model;
    }

    public function addBeforeEachMethod(HookMethodModel $model) : void {
        $this->beforeEachMethodModels[] = $model;
    }

    public function addAfterAllMethod(HookMethodModel $afterAllMethodModel) : void {
        $this->afterAllMethodModels[] = $afterAllMethodModel;
    }

    public function addAfterEachMethod(HookMethodModel $afterEachMethodModel) : void {
        $this->afterEachMethodModels[] = $afterEachMethodModel;
    }

}