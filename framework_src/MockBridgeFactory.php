<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface MockBridgeFactory {

    public function make(string $mockBridgeClass) : MockBridge;

}