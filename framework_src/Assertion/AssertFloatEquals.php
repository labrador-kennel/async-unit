<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\BinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionMessage;

final class AssertFloatEquals extends AbstractAssertion implements Assertion {
    protected function getSummary() : AssertionMessage {
        return new BinaryOperandSummary($this->getExpected(), $this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new BinaryOperandSummary($this->getExpected(), $this->getActual());
    }
}