<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use Cspray\Labrador\AsyncUnit\AssertionMessage;

class EmptyUnaryOperandDetails extends UnaryOperandDetails implements AssertionMessage {

    protected function getExpectedDescriptor() : string {
        return 'empty';
    }
}