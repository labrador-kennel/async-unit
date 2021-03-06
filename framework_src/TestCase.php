<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Context\ExpectationContext;
use Cspray\Labrador\AsyncUnit\Context\TestExpector;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;

/**
 * Represents a type that acts a collection of #[Test] methods to be ran as well as the code necessary to support
 * running each #[Test].
 *
 * The TestCase is an abstract type as opposed to an interface as there are specific concrete functionalities that are
 * expected to be provided by a TestCase to ensure proper running of a test suite.
 */
abstract class TestCase {

    /**
     * A private constructor to ensure that the TestSuiteRunner has complete control over the invocation of a TestCase
     * object creation.
     *
     * Due to the nature of the functionality exposed by this library there are aspects of running a TestCase that need
     * hard, concrete implementation details that do not adhere well to the concept of an interface. This TestCase is
     * intentionally designed to lockdown the internal functionality required by the specification of the framework
     * while keeping open things that are useful in the context of writing unit tests.
     */
    private function __construct(
        private TestSuite $testSuite,
        private AssertionContext $assertionContext,
        private AsyncAssertionContext $asyncAssertionContext,
        private ExpectationContext $expectationContext,
        private ?MockBridge $testMocker = null
    ) {}

    final public function testSuite() : TestSuite {
        return $this->testSuite;
    }

    final public function getAssertionCount() : int {
        return $this->assertionContext->getAssertionCount();
    }

    final public function getAsyncAssertionCount() : int {
        return $this->asyncAssertionContext->getAssertionCount();
    }

    final protected function assert() : AssertionContext {
        return $this->setAssertionFileAndLine($this->assertionContext, __FUNCTION__, debug_backtrace(10));
    }

    final protected function asyncAssert() : AsyncAssertionContext {
        return $this->setAssertionFileAndLine($this->asyncAssertionContext, __FUNCTION__, debug_backtrace(10));
    }

    final protected function expect() : TestExpector {
        return $this->expectationContext;
    }

    final public function mocks() : MockBridge {
        if (is_null($this->testMocker)) {
            $msg = 'Attempted to create a mock but no MockBridge was defined. Please ensure you\'ve configured a mockBridge in your configuration.';
            throw new InvalidStateException($msg);
        }

        return $this->testMocker;
    }

    private function setAssertionFileAndLine(AssertionContext|AsyncAssertionContext $context, string $method, array $backtrace) : AssertionContext|AsyncAssertionContext {
        foreach ($backtrace as $trace) {
            if (!isset($trace['class']) && !isset($trace['function'])) {
                continue;
            }
            if ($trace['class'] === self::class && $trace['function'] === $method) {
                $context->setLastAssertionFile($trace['file']);
                $context->setLastAssertionLine($trace['line']);
                break;
            }
        }
        return $context;
    }

}