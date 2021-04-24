<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Constraint;

use Cspray\Labrador\AsyncUnit\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestSuiteModel;
use Cspray\Labrador\Exception\InvalidArgumentException;
use PHPUnit\Framework\Constraint\Constraint;

class TestSuiteModelHasTestCaseModel extends Constraint {

    public function __construct(private string $expectedClass) {}

    protected function matches($other) : bool {
        if (!$other instanceof TestSuiteModel) {
            $msg = sprintf('You must pass a %s to %s', TestSuiteModel::class, self::class);
            throw new InvalidArgumentException($msg);
        }
        $testCases = $other->getTestCaseModels();
        if (empty($testCases)) {
            return false;
        }

        $testCaseClasses = array_map(fn(TestCaseModel $model) => $model->getTestCaseClass(), $testCases);
        return in_array($this->expectedClass, $testCaseClasses, true);
    }

    public function toString() : string {
        return sprintf('TestSuite has TestCase class "%s"', $this->expectedClass);
    }
}