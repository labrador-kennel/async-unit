<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Cspray\Labrador\AsyncUnit\Assertion\AssertStringEquals;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;

/**
 * Represents an object created for every #[Test] that provides access to the Assertion API as well as the mechanism for
 * which the TestSuiteRunner verifies the appropriate number of Assertion have taken place.
 *
 * You should not be instantiating this object directly. Instead you should be accessing it from the TestCase::assert
 * method.
 */
final class AssertionContext {

    private int $count = 0;

    private ?AssertionComparisonDisplay $lastFailedAssertionDisplay = null;

    private function __construct() {}

    public function getAssertionCount() : int {
        return $this->count;
    }

    public function stringEquals(string $expected, string $actual, string $message = null) : void {
        $this->count++;
        $assertString = new AssertStringEquals($expected);
        $results = $assertString->assert($actual, $message);
        if (!$results->isSuccessful()) {
            $this->lastFailedAssertionDisplay = $results->getComparisonDisplay();
            throw new TestFailedException($results->getErrorMessage());
        }
    }

    public function getFailedAssertionComparisonDisplay() : ?AssertionComparisonDisplay {
        return $this->lastFailedAssertionDisplay;
    }

}