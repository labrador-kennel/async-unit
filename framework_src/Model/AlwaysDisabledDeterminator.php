<?php

namespace Cspray\Labrador\AsyncUnit\Model;

class AlwaysDisabledDeterminator implements DisabledDeterminator {

    private object $context;

    public function __construct(private ?string $reason) {}

    public function isDisabled(): bool {
        return true;
    }

    public function getReason(): ?string {
        return $this->reason;
    }

    public function withContext(object $context): DisabledDeterminator {
        // TODO: Implement withContext() method.
    }

    public function getContext() : object {

    }

}