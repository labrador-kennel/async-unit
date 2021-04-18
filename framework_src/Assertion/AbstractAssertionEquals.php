<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionResult;

abstract class AbstractAssertionEquals implements Assertion {

    final public function assert(mixed $actual, string $errorMessage = null) : AssertionResult {
        if (!$this->isValidType($actual)) {
            $errorMessage = $errorMessage ?? $this->getDefaultInvalidTypeMessage(gettype($actual));
            return AssertionResultFactory::invalidAssertion(
                $errorMessage,
                $this->getAssertionComparisonDisplay($actual)
            );
        } else if ($this->getExpected() !== $actual) {
            return AssertionResultFactory::invalidAssertion(
                $errorMessage ?? $this->getDefaultInvalidComparisonMessage($actual),
                $this->getAssertionComparisonDisplay($actual)
            );
        }

        return AssertionResultFactory::validAssertion();
    }

    protected function getDefaultInvalidTypeMessage(string $actualType) : string {
        return sprintf('Failed asserting that a value with type "%s" is comparable to type "%s".', $actualType, $this->getExpectedType());
    }

    protected function getDefaultInvalidComparisonMessage($actual) : string {
        return sprintf('Failed comparing that 2 %ss are equal to one another', $this->getExpectedType());
    }

    abstract protected function isValidType(mixed $actual) : bool;

    abstract protected function getExpectedType() : string;

    abstract protected function getExpected();

    abstract protected function getAssertionComparisonDisplay($actual) : AssertionComparisonDisplay;
}