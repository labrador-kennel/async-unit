<?php declare(strict_types=1);

namespace Acme\Examples\CustomAssertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionResult;

class MyAssertion implements Assertion {

    public function __construct(private mixed $actual) {}

    public function assert() : AssertionResult {
        $assert = new Assertion\AssertStringEquals('AsyncUnit', $this->actual);
        return $assert->assert();
    }

}