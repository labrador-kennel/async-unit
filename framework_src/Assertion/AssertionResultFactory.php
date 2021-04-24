<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\AssertionResult;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;

final class AssertionResultFactory {

    private function __construct() {}

    public static function validAssertion(
        string $assertionString,
        string $notAssertionString,
        AssertionComparisonDisplay $assertionComparisonDisplay
    ) : AssertionResult {
        return new class($assertionString, $notAssertionString, $assertionComparisonDisplay) implements AssertionResult {

            public function __construct(
                private string $assertionString,
                private string $notAssertionString,
                private AssertionComparisonDisplay $assertionComparisonDisplay
            ) {}

            public function isSuccessful() : bool {
                return true;
            }

            public function getAssertionString() : string {
                return $this->assertionString;
            }

            public function getNotAssertionString() : string {
                return $this->notAssertionString;
            }

            public function getComparisonDisplay() : AssertionComparisonDisplay {
                return $this->assertionComparisonDisplay;
            }

        };
    }

    public static function invalidAssertion(
        string $assertionString,
        string $notAssertionString,
        AssertionComparisonDisplay $comparisonDisplay
    ) : AssertionResult {
        return new class($assertionString, $notAssertionString, $comparisonDisplay) implements AssertionResult {

            public function __construct(
                private string $assertionString,
                private string $notAssertionString,
                private AssertionComparisonDisplay $comparisonDisplay
            ) {}

            public function isSuccessful() : bool {
                return false;
            }

            public function getAssertionString() : string {
                return $this->assertionString;
            }

            public function getNotAssertionString() : string {
                return $this->notAssertionString;
            }

            public function getComparisonDisplay() : AssertionComparisonDisplay {
                return $this->comparisonDisplay;
            }
        };
    }

}

