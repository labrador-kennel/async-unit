<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Constraint;

use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\Exception\InvalidArgumentException;
use PHPUnit\Framework\Constraint\Constraint;

class TestCaseModelHasTestMethod extends Constraint {

    public function __construct(private string $testClass, private string $method) {}

    protected function matches($other) : bool {
        if (!$other instanceof TestCaseModel) {
            throw new InvalidArgumentException(sprintf(
                'You must pass a %s to %s',
                TestCaseModel::class,
                TestCaseModelHasTestMethod::class
            ));
        }
        if ($this->testClass !== $other->getClass()) {
            return false;
        }
        $testMethods = $other->getTestModels();
        foreach ($testMethods as $testMethod) {
            if ($testMethod->getMethod() === $this->method) {
                return true;
            }
        }

        return false;
    }

    public function toString() : string {
        return '';
    }
}