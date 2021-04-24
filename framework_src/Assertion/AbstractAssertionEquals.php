<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionResult;

abstract class AbstractAssertionEquals implements Assertion {

    final public function assert() : AssertionResult {
        $actual = $this->getActual();
        if (!$this->isValidType($actual)) {
            return AssertionResultFactory::invalidAssertion(
                $this->getInvalidTypeAssertionString(gettype($actual)),
                $this->getNotAssertionString($actual),
                $this->getAssertionComparisonDisplay($actual)
            );
        } else if ($this->getExpected() !== $actual) {
            return AssertionResultFactory::invalidAssertion(
                $this->getAssertionString($actual),
                $this->getNotAssertionString($actual),
                $this->getAssertionComparisonDisplay($actual)
            );
        }

        return AssertionResultFactory::validAssertion(
            $this->getAssertionString($actual),
            $this->getNotAssertionString($actual),
            $this->getAssertionComparisonDisplay($actual)
        );
    }

    protected function getInvalidTypeAssertionString(string $actualType) : string {
        return sprintf('asserting that a value with type "%s" is comparable to type "%s".', $actualType, $this->getExpectedType());
    }

    protected function getAssertionString($actual) : string {
        return sprintf('comparing that 2 %ss are equal to one another', $this->getExpectedType());
    }

    protected function getNotAssertionString($actual) : string {
        return sprintf('comparing that 2 %ss are not equal to one another', $this->getExpectedType());
    }

    abstract protected function isValidType(mixed $actual) : bool;

    abstract protected function getExpectedType() : string;

    abstract protected function getExpected();

    abstract protected function getActual();

    abstract protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay;
}