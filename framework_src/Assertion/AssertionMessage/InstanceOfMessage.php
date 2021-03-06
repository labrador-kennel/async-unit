<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use Cspray\Labrador\AsyncUnit\AssertionMessage;

final class InstanceOfMessage implements AssertionMessage {

    public function __construct(
        private string|object $expected,
        private object $actual
    ) {}

    public function toString() : string {
        return sprintf(
            'asserting object with type "%s" is an instanceof %s',
            $this->actual::class,
            is_object($this->expected) ? get_class($this->expected) : $this->expected
        );
    }

    public function toNotString() : string {
        return sprintf(
            'asserting object with type "%s" is not an instanceof %s',
            $this->actual::class,
            is_object($this->expected) ? get_class($this->expected) : $this->expected
        );
    }
}