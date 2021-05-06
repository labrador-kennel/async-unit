<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\NullUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\NullUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionMessage;

class AssertIsNull extends AbstractAssertion implements Assertion {

    public function __construct(mixed $actual) {
        parent::__construct(null, $actual);
    }

    protected function isValidType(mixed $actual) : bool {
        return is_null($actual);
    }

    protected function getSummary() : AssertionMessage {
        return new NullUnaryOperandSummary($this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new NullUnaryOperandDetails($this->getActual());
    }

    protected function getInvalidTypeSummary() : AssertionMessage {
        return new NullUnaryOperandSummary($this->getActual());
    }

}