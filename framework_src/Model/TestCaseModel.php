<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

final class TestCaseModel {

    use HookAware;
    use CanBeDisabledTrait;

    private array $testMethodModels = [];

    public function __construct(
        private string $testCaseClass,
        private ?string $testSuiteClass = null
    ) {}

    public function getTestSuiteClass() : ?string {
        return $this->testSuiteClass;
    }

    public function setTestSuiteClass(string $testSuiteClass) : void {
        $this->testSuiteClass = $testSuiteClass;
    }

    public function getClass() : string {
        return $this->testCaseClass;
    }

    public function addTestMethodModel(TestModel $testMethodModel) : void {
        $this->testMethodModels[] = $testMethodModel;
    }

    /**
     * @return TestModel[]
     */
    public function getTestMethodModels() : array {
        return $this->testMethodModels;
    }

}