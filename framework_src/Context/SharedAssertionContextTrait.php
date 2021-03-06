<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Cspray\Labrador\AsyncUnit\AssertionResult;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;

trait SharedAssertionContextTrait {

    private int $count = 0;

    private bool $isNot = false;

    public function getAssertionCount() : int {
        return $this->count;
    }

    public function not() : self {
        $this->isNot = true;
        return $this;
    }

    private function getDefaultFailureMessage(string $assertionString) : string {
        return sprintf("Failed %s", $assertionString);
    }

    private function invokedAssertionContext() : void {
        $this->count++;
        $this->isNot = false;
    }

    private function handleAssertionResults(AssertionResult $result, bool $isNot, ?string $customMessage) {
        if (($isNot && $result->isSuccessful()) || (!$isNot && !$result->isSuccessful())) {
            throw new AssertionFailedException(
                $customMessage ?? $this->getDefaultFailureMessage($isNot ? $result->getSummary()->toNotString() : $result->getSummary()->toString()),
                $this->getDefaultFailureMessage($isNot ? $result->getDetails()->toNotString() : $result->getDetails()->toString()),
                $this->getLastAssertionFile(),
                $this->getLastAssertionLine()
            );
        }
    }

}