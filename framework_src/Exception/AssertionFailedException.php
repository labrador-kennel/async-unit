<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Exception;

final class AssertionFailedException extends TestFailedException {

    public function __construct(
        string $summary,
        private string $detailedMessage,
        private string $assertionFailureFile,
        private int $assertionFailureLine
    ) {
        parent::__construct($summary);
    }

    public function getDetailedMessage() : string {
        return $this->detailedMessage;
    }

    public function getAssertionFailureFile() : string {
        return $this->assertionFailureFile;
    }

    public function getAssertionFailureLine() : int {
        return $this->assertionFailureLine;
    }

}