<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\FalseUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\FalseUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionMessage;

class AssertIsFalse extends AbstractAssertion implements Assertion {

    public function __construct(mixed $actual) {
        parent::__construct(false, $actual);
    }

    protected function isValidType(mixed $actual) : bool {
        return is_bool($actual);
    }

    protected function getSummary() : AssertionMessage {
        return new FalseUnaryOperandSummary($this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new FalseUnaryOperandDetails($this->getActual());
    }

    protected function getInvalidTypeSummary() : AssertionMessage {
        return new FalseUnaryOperandSummary($this->getActual());
    }
}