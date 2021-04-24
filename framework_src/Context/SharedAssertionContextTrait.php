<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

trait SharedAssertionContextTrait {

    private int $count = 0;

    private bool $isNot = false;

    private function __construct() {}

    public function getAssertionCount() : int {
        return $this->count;
    }

    public function not() : self {
        $this->isNot = true;
        return $this;
    }

    private function getDefaultFailureMessage(string $assertionString) {
        return sprintf("Failed %s", $assertionString);
    }

}