<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

final class AssertionResultFactory {

    private function __construct() {}

    public static function invalidAssertion(string $message) : AssertionResult {
        return new class($message) implements AssertionResult {

            public function __construct(private string $message) {}

            public function isSuccessful() : bool {
                return false;
            }

            public function getErrorMessage() : string {
                return $this->message;
            }

            public function getBacktrace() : array {
                // TODO: Implement getBacktrace() method.
            }

            public function getComparisonDisplay() : ?AssertionComparisonDisplay {
                // TODO: Implement getComparisonDisplay() method.
            }
        };
    }


}

