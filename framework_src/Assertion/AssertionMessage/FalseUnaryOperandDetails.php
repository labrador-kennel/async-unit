<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

final class FalseUnaryOperandDetails extends UnaryOperandDetails {

    protected function getExpectedDescriptor() : string {
        return 'false';
    }
}