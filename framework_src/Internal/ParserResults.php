<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal;

use Cspray\Labrador\AsyncUnit\Internal\Model\TestCaseModel;

/**
 * @internal
 */
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