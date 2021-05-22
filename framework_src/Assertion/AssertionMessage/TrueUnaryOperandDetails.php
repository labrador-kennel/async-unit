<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

final class TrueUnaryOperandDetails extends UnaryOperandDetails {

    protected function getExpectedDescriptor() : string {
        return 'true';
    }
}