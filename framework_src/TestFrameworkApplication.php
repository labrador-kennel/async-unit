<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit;


use Amp\Promise;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Internal\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncUnit\Internal\InternalEventNames;
use Cspray\Labrador\AsyncUnit\Internal\Parser;
use Cspray\Labrador\AsyncUnit\Internal\ParserResult;
use Cspray\Labrador\AsyncUnit\Internal\TestSuiteRunner;
use Cspray\Labrador\Plugin\Pluggable;
use PHPUnit\Framework\Assert;
use function Amp\call;

final class TestFrameworkApplication extends AbstractApplication {

    private EventEmitter $emitter;
    private ParserResult $parserResult;
    private TestSuiteRunner $testSuiteRunner;

    public function __construct(
        Pluggable $pluggable,
        EventEmitter $emitter,
        ParserResult $parserResult,
        TestSuiteRunner $testSuiteRunner
    ) {
        parent::__construct($pluggable);
        $this->emitter = $emitter;
        $this->parserResult = $parserResult;
        $this->testSuiteRunner = $testSuiteRunner;
    }

    protected function doStart() : Promise {
        return call(function() {
            $testSuites = $this->parserResult->getTestSuiteModels();

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function(TestInvokedEvent $testInvokedEvent) {
                $invokedTestModel = $testInvokedEvent->getTarget();
                $testPassed = is_null($testInvokedEvent->getTarget()->getFailureException());
                $testResult = $this->getTestResult(
                    $invokedTestModel->getTestCase(),
                    $invokedTestModel->getMethod(),
                    $testInvokedEvent->getTarget()->getFailureException()
                );

                if ($testPassed) {
                    yield $this->emitter->emit(new TestPassedEvent($testResult));
                } else {
                    yield $this->emitter->emit(new TestFailedEvent($testResult));
                }
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            yield $this->emitter->emit(
                new StandardEvent(Events::TEST_PROCESSING_FINISHED_EVENT, new \stdClass())
            );
        });
    }

    private function getTestResult(
        TestCase $testCase,
        string $method,
        ?TestFailedException $testFailedException
    ) : TestResult {
        return new class($testCase, $method, $testFailedException) implements TestResult {

            public function __construct(
                private TestCase $testCase,
                private string $method,
                private ?TestFailedException $testFailedException
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

            public function getFailureException() : TestFailedException|AssertionFailedException {
                if (is_null($this->testFailedException)) {
                    throw new InvalidStateException('Attempted to access a TestFailedException on a successful TestResult.');
                }
                return $this->testFailedException;
            }
        };
    }

}