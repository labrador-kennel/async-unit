<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

final class TestCaseModel {

    use HookAware;

    private array $testMethodModels = [];

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

    public function addTestMethodModel(TestMethodModel $testMethodModel) : void {
        $this->testMethodModels[] = $testMethodModel;
    }

    /**
     * @return TestMethodModel[]
     */
    public function getTestMethodModels() : array {
        return $this->testMethodModels;
    }

}