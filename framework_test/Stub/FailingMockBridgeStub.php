<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Cspray\Labrador\AsyncUnit\Exception\MockFailureException;
use Cspray\Labrador\AsyncUnit\MockBridge;

class FailingMockBridgeStub implements MockBridge {

    public function initialize(): void {
        // TODO: Implement initialize() method.
    }

    public function finalize(): void {
        throw new MockFailureException('Thrown from the FailingMockBridgeStub');
    }

    public function createMock(string $class): object {
        return new \stdClass();
    }

    public function getAssertionCount(): int {
        return 0;
    }
}