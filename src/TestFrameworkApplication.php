<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncTesting;


use Amp\Promise;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncTesting\Event\TestFailedEvent;
use Cspray\Labrador\AsyncTesting\Event\TestPassedEvent;
use Cspray\Labrador\AsyncTesting\Exception\InvalidStateException;
use Cspray\Labrador\AsyncTesting\Exception\TestFailedException;
use Cspray\Labrador\AsyncTesting\Internal\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncTesting\Internal\InternalEventNames;
use Cspray\Labrador\AsyncTesting\Internal\Parser;
use Cspray\Labrador\AsyncTesting\Internal\TestSuiteRunner;
use Cspray\Labrador\Plugin\Pluggable;
use PHPUnit\Framework\Assert;
use function Amp\call;

class TestFrameworkApplication extends AbstractApplication {

    private array $testDirectories;

    private Parser $parser;
    private EventEmitter $emitter;
    private TestSuiteRunner $testSuiteRunner;

    public function __construct(Pluggable $pluggable, EventEmitter $emitter, array $testDirectories) {
        parent::__construct($pluggable);
        $this->testDirectories = $testDirectories;
        $this->parser = new Parser();
        $this->emitter = $emitter;
        $this->testSuiteRunner = new TestSuiteRunner($emitter);
    }

    protected function doStart() : Promise {
        return call(function() {
            $testSuites = $this->parser->parse($this->testDirectories);

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function(TestInvokedEvent $testInvokedEvent) {
                $invokedTestModel = $testInvokedEvent->getTarget();
                $testPassed = is_null($testInvokedEvent->getTarget()->getFailureException());
                $testResult = $this->getTestResult(
                    $invokedTestModel->getTestCase(),
                    $invokedTestModel->getMethod(),
                    $testInvokedEvent->getTarget()->getFailureException(),
                    $testInvokedEvent->getTarget()->getAssertionComparisonDisplay()
                );

                if ($testPassed) {
                    yield $this->emitter->emit(new TestPassedEvent($testResult));
                } else {
                    yield $this->emitter->emit(new TestFailedEvent($testResult));
                }
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    private function getTestResult(
        TestCase $testCase,
        string $method,
        ?TestFailedException $testFailedException,
        ?AssertionComparisonDisplay $comparisonDisplay
    ) : TestResult {
        return new class($testCase, $method, $testFailedException, $comparisonDisplay) implements TestResult {

            public function __construct(
                private TestCase $testCase,
                private string $method,
                private ?TestFailedException $testFailedException,
                private ?AssertionComparisonDisplay $comparisonDisplay
            ) {}

            public function getTestCase() : TestCase {
                return $this->testCase;
            }

            public function getTestMethod() : string {
                return $this->method;
            }

            public function isSuccessful() : bool {
                return is_null($this->testFailedException);
            }

            public function getFailureException() : TestFailedException {
                if (is_null($this->testFailedException)) {
                    throw new InvalidStateException('Attempted to access a TestFailedException on a successful TestResult.');
                }
                return $this->testFailedException;
            }

            public function getAssertionComparisonDisplay() : ?AssertionComparisonDisplay {
                return $this->comparisonDisplay;
            }
        };
    }

}