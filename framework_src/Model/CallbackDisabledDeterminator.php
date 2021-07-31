<?php

namespace Cspray\Labrador\AsyncUnit\Model;

class CallbackDisabledDeterminator implements DisabledDeterminator {

    private $callback;

    public function __construct(
        callable $callback,
        private ?string $reason
    ) {
        $this->callback = $callback;
    }

    public function isDisabled(): bool {
        // TODO: Implement isDisabled() method.
    }

    public function getReason(): ?string {
        // TODO: Implement getReason() method.
    }
}