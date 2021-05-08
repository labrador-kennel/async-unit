<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\BinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\InvalidTypeBinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionMessage;

final class AssertFloatEquals extends AbstractAssertion implements Assertion {

    protected function isValidType(mixed $actual) : bool {
        return is_float($actual);
    }

    protected function getSummary() : AssertionMessage {
        // TODO: Implement getSummary() method.
        return new BinaryOperandSummary($this->getExpected(), $this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        // TODO: Implement getDetails() method.
        return new BinaryOperandSummary($this->getExpected(), $this->getActual());
    }
    protected function getInvalidTypeSummary() : AssertionMessage {
        return new InvalidTypeBinaryOperandSummary($this->getExpected(), $this->getActual());
    }

}