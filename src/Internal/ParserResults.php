<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncTesting\Internal;


use Cspray\Labrador\AsyncTesting\Internal\Model\TestCaseModel;

class ParserResults {


    public function __construct(
        private array $testCaseModels
    ) {}

    /**
     * @return TestCaseModel[]
     */
    public function getTestCaseModels() : array {
        return $this->testCaseModels;
    }

}