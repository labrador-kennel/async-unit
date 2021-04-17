<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

final class AssertionResultFactory {

    private function __construct() {}

    public static function validAssertion() : AssertionResult {
        return new class implements AssertionResult {

            public function isSuccessful() : bool {
                return true;
            }

            public function getErrorMessage() : ?string {
                return null;
            }

            public function getComparisonDisplay() : ?AssertionComparisonDisplay {
                return null;
            }
        };
    }

    public static function invalidAssertion(string $message, AssertionComparisonDisplay $comparisonDisplay) : AssertionResult {
        return new class($message, $comparisonDisplay) implements AssertionResult {

            public function __construct(
                private string $message,
                private AssertionComparisonDisplay $comparisonDisplay
            ) {}

            public function isSuccessful() : bool {
                return false;
            }

            public function getErrorMessage() : string {
                return $this->message;
            }

            public function getComparisonDisplay() : ?AssertionComparisonDisplay {
                return $this->comparisonDisplay;
            }
        };
    }


}

