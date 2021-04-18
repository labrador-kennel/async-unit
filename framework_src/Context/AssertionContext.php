<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Cspray\Labrador\AsyncUnit\Assertion\AssertArrayEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertFloatEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIntEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsFalse;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsNull;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsTrue;
use Cspray\Labrador\AsyncUnit\Assertion\AssertStringEquals;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Internal\LastAssertionCalledTrait;

/**
 * Represents an object created for every #[Test] that provides access to the Assertion API as well as the mechanism for
 * which the TestSuiteRunner verifies the appropriate number of Assertion have taken place.
 *
 * You should not be instantiating this object directly. Instead you should be accessing it from the TestCase::assert
 * method.
 */
final class AssertionContext {

    use LastAssertionCalledTrait;

    private int $count = 0;

    private function __construct() {}

    public function getAssertionCount() : int {
        return $this->count;
    }

    public function arrayEquals(array $expected, array $actual, string $message = null) : void {
        $this->count++;
        $assert = new AssertArrayEquals($expected);
        $results = $assert->assert($actual, $message);
        if (!$results->isSuccessful()) {
            throw new AssertionFailedException(
                $results->getErrorMessage(),
                $results->getComparisonDisplay(),
                $this->getLastAssertionFile(),
                $this->getLastAssertionLine()
            );
        }
    }

    public function floatEquals(float $expected, float $actual, string $message = null) : void {
        $this->count++;
        $assert = new AssertFloatEquals($expected);
        $results = $assert->assert($actual, $message);
        if (!$results->isSuccessful()) {
            throw new AssertionFailedException(
                $results->getErrorMessage(),
                $results->getComparisonDisplay(),
                $this->getLastAssertionFile(),
                $this->getLastAssertionLine()
            );
        }
    }

    public function intEquals(int $expected, int $actual, string $message = null) : void {
        $this->count++;
        $assert = new AssertIntEquals($expected);
        $results = $assert->assert($actual, $message);
        if (!$results->isSuccessful()) {
            throw new AssertionFailedException(
                $results->getErrorMessage(),
                $results->getComparisonDisplay(),
                $this->getLastAssertionFile(),
                $this->getLastAssertionLine()
            );
        }
    }

    public function stringEquals(string $expected, string $actual, string $message = null) : void {
        $this->count++;
        $assert = new AssertStringEquals($expected);
        $results = $assert->assert($actual, $message);
        if (!$results->isSuccessful()) {
            throw new AssertionFailedException(
                $results->getErrorMessage(),
                $results->getComparisonDisplay(),
                $this->getLastAssertionFile(),
                $this->getLastAssertionLine()
            );
        }
    }

    public function isTrue(bool $actual, string $message = null) : void {
        $this->count++;
        $assert = new AssertIsTrue();
        $results = $assert->assert($actual, $message);
        if (!$results->isSuccessful()) {
            throw new AssertionFailedException(
                $results->getErrorMessage(),
                $results->getComparisonDisplay(),
                $this->getLastAssertionFile(),
                $this->getLastAssertionLine()
            );
        }
    }

    public function isFalse(bool $actual, string $message = null) : void {
        $this->count++;
        $assert = new AssertIsFalse();
        $results = $assert->assert($actual, $message);
        if (!$results->isSuccessful()) {
            throw new AssertionFailedException(
                $results->getErrorMessage(),
                $results->getComparisonDisplay(),
                $this->getLastAssertionFile(),
                $this->getLastAssertionLine()
            );
        }
    }

    public function isNull($actual, string $message = null) : void {
        $this->count++;
        $assert = new AssertIsNull();
        $results = $assert->assert($actual, $message);
        if (!$results->isSuccessful()) {
            throw new AssertionFailedException(
                $results->getErrorMessage(),
                $results->getComparisonDisplay(),
                $this->getLastAssertionFile(),
                $this->getLastAssertionLine()
            );
        }
    }

}