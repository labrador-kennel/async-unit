<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Exception;

use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Throwable;

final class AssertionFailedException extends TestFailedException {

    private AssertionComparisonDisplay $comparisonDisplay;

    public function __construct(string $message, AssertionComparisonDisplay $comparisonDisplay) {
        parent::__construct($message);
        $this->comparisonDisplay = $comparisonDisplay;
    }

    public function getComparisonDisplay() : AssertionComparisonDisplay {
        return $this->comparisonDisplay;
    }

    public function isAssertionFailure() : bool {
        return true;
    }

}