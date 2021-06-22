<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Auryn\Injector;

class SupportedMockBridgeFactory implements MockBridgeFactory {

    private Injector $injector;

    public function __construct(Injector $injector) {
        $this->injector = $injector;
    }

    public function make(string $mockBridgeClass): MockBridge {
        return $this->injector->make($mockBridgeClass);
    }

}