<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use Cspray\Labrador\AsyncUnit\AssertionMessage;

class InvalidTypeBinaryOperandSummary implements AssertionMessage {

    public function __construct(private mixed $expected, private mixed $actual) {}

    public function toString() : string {
        return sprintf(
            'asserting actual value with type "%s" is comparable to type "%s"',
            strtolower(gettype($this->actual)),
            strtolower(gettype($this->expected))
        );
    }

    public function toNotString() : string {
        return '';
    }
}