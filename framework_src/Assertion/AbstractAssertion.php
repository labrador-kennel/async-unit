<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionMessage;
use Cspray\Labrador\AsyncUnit\AssertionResult;

abstract class AbstractAssertion implements Assertion {

    public function __construct(private mixed $expected, private mixed $actual) {}

    final public function assert() : AssertionResult {
        if (!$this->isValidType($this->actual)) {
            $summaryToString = sprintf(
                'asserting value with type "%s" is comparable to type "%s"',
                gettype($this->actual),
                gettype($this->expected)
            );
            $summaryNotString = sprintf(
                'asserting value with type "%s" is not comparable to type "%s"',
                gettype($this->expected),
                gettype($this->actual)
            );
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

    private function createAssertionMessage(string $toString, string $toNotString) : AssertionMessage {
        return new class($toString, $toNotString) implements AssertionMessage {
            public function __construct(private string $toString, private string $toNotString) {}

            public function toString() : string {
                return $this->toString;
            }

            public function toNotString() : string {
                return $this->toNotString;
            }
        };
    }

}