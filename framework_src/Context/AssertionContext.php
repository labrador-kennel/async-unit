<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Cspray\Labrador\AsyncUnit\Assertion\AssertArrayEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertFloatEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIntEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsFalse;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsNull;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsTrue;
use Cspray\Labrador\AsyncUnit\Assertion\AssertStringEquals;
use Cspray\Labrador\AsyncUnit\LastAssertionCalledTrait;

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

    private function __construct(private CustomAssertionContext $customAssertionContext) {}

    public function arrayEquals(array $expected, array $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertArrayEquals($expected, $actual);
        $results = $assert->assert();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function floatEquals(float $expected, float $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertFloatEquals($expected, $actual);
        $results = $assert->assert();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function intEquals(int $expected, int $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertIntEquals($expected, $actual);
        $results = $assert->assert();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function stringEquals(string $expected, string $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertStringEquals($expected, $actual);
        $results = $assert->assert();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isTrue(bool $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertIsTrue($actual);
        $results = $assert->assert();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isFalse(bool $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertIsFalse($actual);
        $results = $assert->assert();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isNull(mixed $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $assert = new AssertIsNull($actual);
        $results = $assert->assert();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function __call(string $methodName, array $args) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $results = $this->customAssertionContext->createAssertion($methodName, ...$args)->assert();

        $this->handleAssertionResults($results, $isNot, null);
    }

}