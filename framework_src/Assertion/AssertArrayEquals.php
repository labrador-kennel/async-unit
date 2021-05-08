<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionMessage;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\BinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\InvalidTypeBinaryOperandSummary;

final class AssertArrayEquals extends AbstractAssertion implements Assertion {

    protected function isValidType(mixed $actual) : bool {
        return is_array($actual);
    }

    protected function getSummary() : AssertionMessage {
        return new BinaryOperandSummary($this->getExpected(), $this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new BinaryOperandSummary($this->getExpected(), $this->getActual());
    }

    protected function getInvalidTypeSummary() : AssertionMessage {
        return new InvalidTypeBinaryOperandSummary($this->getExpected(), $this->getActual());
    }
}