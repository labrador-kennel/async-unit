<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Cspray\Labrador\AsyncUnit\MockBridge;
use Cspray\Labrador\AsyncUnit\MockBridgeFactory;

class FailingMockBridgeFactory implements MockBridgeFactory {

    public function make(string $mockBridgeClass): MockBridge {
        return new FailingMockBridgeStub();
    }
}