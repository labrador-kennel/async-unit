<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Cspray\Labrador\AsyncUnit\Assertion\AssertArrayEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertFloatEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIntEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsFalse;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsNull;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsTrue;
use Cspray\Labrador\AsyncUnit\Assertion\AssertStringEquals;
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
    use SharedAssertionContextTrait;

    public function arrayEquals(array $expected, array $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertArrayEquals($expected);
        $results = $assert->assert($actual);

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function floatEquals(float $expected, float $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertFloatEquals($expected);
        $results = $assert->assert($actual);

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function intEquals(int $expected, int $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertIntEquals($expected);
        $results = $assert->assert($actual);

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function stringEquals(string $expected, string $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertStringEquals($expected);
        $results = $assert->assert($actual);

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isTrue(bool $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertIsTrue();
        $results = $assert->assert($actual);

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isFalse(bool $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertIsFalse();
        $results = $assert->assert($actual);

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isNull($actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertIsNull();
        $results = $assert->assert($actual);

        $this->handleAssertionResults($results, $isNot, $message);
    }

}