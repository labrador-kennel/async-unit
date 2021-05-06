<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionMessage;
use Cspray\Labrador\AsyncUnit\AssertionResult;

abstract class AbstractAssertion implements Assertion {

    public function __construct(private mixed $expected, private mixed $actual) {}

    final public function assert() : AssertionResult {
        if (!$this->isValidType($this->actual)) {
            return AssertionResultFactory::invalidAssertion(
                $this->getInvalidTypeSummary(),
                $this->getDetails()
            );
        } else if ($this->expected !== $this->actual) {
            return AssertionResultFactory::invalidAssertion($this->getSummary(), $this->getDetails());
        }

        return AssertionResultFactory::validAssertion($this->getSummary(), $this->getDetails());
    }

    abstract protected function isValidType(mixed $actual) : bool;

    abstract protected function getInvalidTypeSummary() : AssertionMessage;

    abstract protected function getSummary() : AssertionMessage;

    abstract protected function getDetails() : AssertionMessage;

    protected function getExpected() : mixed {
        return $this->expected;
    }

    protected function getActual() : mixed {
        return $this->actual;
    }

}