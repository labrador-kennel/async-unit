<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Exception;

use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

final class AssertionFailedException extends TestFailedException {

    private AssertionComparisonDisplay $comparisonDisplay;
    private string $assertionFailureFile;
    private int $assertionFailureLine;

    public function __construct(
        string $message,
        AssertionComparisonDisplay $comparisonDisplay,
        string $assertionFailureFile,
        int $assertionFailureLine
    ) {
        parent::__construct($message);
        $this->comparisonDisplay = $comparisonDisplay;
        $this->assertionFailureFile = $assertionFailureFile;
        $this->assertionFailureLine  = $assertionFailureLine;
    }

    public function getComparisonDisplay() : AssertionComparisonDisplay {
        return $this->comparisonDisplay;
    }

    public function isAssertionFailure() : bool {
        return true;
    }

    public function getAssertionFailureFile() : string {
        return $this->assertionFailureFile;
    }

    public function getAssertionFailureLine() : int {
        return $this->assertionFailureLine;
    }

}