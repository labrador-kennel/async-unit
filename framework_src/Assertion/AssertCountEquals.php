<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionMessage;
use Cspray\Labrador\AsyncUnit\AssertionResult;

class AssertCountEquals implements Assertion {

    public function __construct(private int $expected, private array $actual) {}

    public function assert() : AssertionResult {
        $factoryMethod = count($this->actual) === $this->expected ? 'validAssertion' : 'invalidAssertion';
        return AssertionResultFactory::$factoryMethod(
            $this->getSummary(),
            $this->getDetails()
        );
    }

    private function getSummary() : AssertionMessage {
        return new Assertion\AssertionMessage\CountEqualsSummary();
    }

    private function getDetails() : AssertionMessage {
        return new Assertion\AssertionMessage\CountEqualsDetails();
    }
}