<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionResult;

final class AssertIsEmpty implements Assertion {

    public function __construct(private mixed $actual) {}

    public function assert() : AssertionResult {
        $factoryMethod = empty($this->actual) ? 'validAssertion' : 'invalidAssertion';
        return AssertionResultFactory::$factoryMethod(
            new Assertion\AssertionMessage\EmptyUnaryOperandSummary($this->actual),
            new Assertion\AssertionMessage\EmptyUnaryOperandDetails($this->actual)
        );
    }
}