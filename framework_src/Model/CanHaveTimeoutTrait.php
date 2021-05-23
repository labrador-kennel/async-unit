<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

trait CanHaveTimeoutTrait {

    private ?int $timeout = null;

    public function setTimeout(int $timeout) : void {
        $this->timeout = $timeout;
    }

    public function getTimeout() : ?int {
        return $this->timeout;
    }

}