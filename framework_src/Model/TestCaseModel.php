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
     * @return HookMethodModel[]
     */
    public function getBeforeAllMethodModels() : array {
        return $this->beforeAllMethodModels;
    }

    /**
     * @return HookMethodModel[]
     */
    public function getBeforeEachMethodModels() : array {
        return $this->beforeEachMethodModels;
    }

    /**
     * @return HookMethodModel[]
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

    public function addAfterAllMethod(HookMethodModel $model) : void {
        $this->afterAllMethodModels[] = $model;
    }

    public function addAfterEachMethod(HookMethodModel $model) : void {
        $this->afterEachMethodModels[] = $model;
    }

}