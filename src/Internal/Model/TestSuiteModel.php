<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Internal\Model;

use Cspray\Labrador\AsyncTesting\TestCase;

class TestSuiteModel {

    private array $testCaseModels;

    public function __construct() {

    }

    public function getName() : string {
        return 'Default TestSuite';
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

}