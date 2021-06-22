<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Cspray\Labrador\AsyncUnit\MockBridge;
use Cspray\Labrador\AsyncUnit\MockBridgeFactory;

class MockBridgeFactoryStub implements MockBridgeFactory {

    private array $createdMocks = [];

    public function make(string $mockBridgeClass): MockBridge {
        $mockBridge = new MockBridgeStub();
        $this->createdMocks[] = ['mockBridgeClass' => $mockBridgeClass, 'mockBridge' => $mockBridge];
        return $mockBridge;
    }

    public function getCreatedMockBridges() : array {
        return $this->createdMocks;
    }
}