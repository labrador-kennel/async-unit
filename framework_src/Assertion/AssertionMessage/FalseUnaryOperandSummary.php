<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

final class FalseUnaryOperandSummary extends UnaryOperandSummary {

    protected function getExpectedDescriptor() : string {
        return 'false';
    }
}