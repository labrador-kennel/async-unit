<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\TrueUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\TrueUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionMessage;

final class AssertIsTrue extends AbstractAssertion implements Assertion {
    public function __construct(mixed $actual) {
        parent::__construct(true, $actual);
    }

    protected function getSummary() : AssertionMessage {
        return new TrueUnaryOperandSummary($this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new TrueUnaryOperandDetails($this->getActual());
    }
}