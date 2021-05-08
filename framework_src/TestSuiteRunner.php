<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Promise;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Event\TestCaseFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestCaseStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestDisabledEvent;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteStartedEvent;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestDisabledException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestOutputException;
use Cspray\Labrador\AsyncUnit\Exception\TestSetupException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestTearDownException;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use ReflectionClass;
use Throwable;
use function Amp\call;

/**
 * @internal
 */
final class TestSuiteRunner {

    private array $reflectionCache = [];

    public function __construct(
        private EventEmitter $emitter,
        private CustomAssertionContext $customAssertionContext,
        private Randomizer $randomizer
    ) {}

    public function runTestSuites(TestSuiteModel... $testSuiteModels) : Promise {
        return call(function() use($testSuiteModels) {
            $testSuiteModels = $this->randomizer->randomize($testSuiteModels);
            foreach ($testSuiteModels as $testSuiteModel) {
                $testSuiteClass = $testSuiteModel->getClass();
                /** @var TestSuite $testSuite */
                $testSuite = (new ReflectionClass($testSuiteClass))->newInstanceWithoutConstructor();

                yield $this->emitter->emit(new TestSuiteStartedEvent($testSuiteModel));
                if (!$testSuiteModel->isDisabled()) {
                    yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::BeforeAll(), TestSuiteSetUpException::class);
                }

                $testCaseModels = $this->randomizer->randomize($testSuiteModel->getTestCaseModels());
                foreach ($testCaseModels as $testCaseModel) {
                    yield $this->emitter->emit(new TestCaseStartedEvent($testCaseModel));
                    if (!$testSuiteModel->isDisabled()) {
                        yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::BeforeEach(), TestSuiteSetUpException::class);
                    }
                    if (!$testCaseModel->isDisabled()) {
                        yield $this->invokeHooks($testCaseModel->getClass(), $testCaseModel, HookType::BeforeAll(), TestCaseSetUpException::class, [$testSuite]);
                    }

                    $testMethodModels = $this->randomizer->randomize($testCaseModel->getTestMethodModels());
                    foreach ($testMethodModels as $testMethodModel) {
                        /** @var AssertionContext $assertionContext */
                        /** @var AsyncAssertionContext $asyncAssertionContext */
                        [$testCase, $assertionContext, $asyncAssertionContext] = $this->invokeTestCaseConstructor($testCaseModel->getClass(), $testSuite);
                        if ($testMethodModel->getDataProvider() !== null) {
                            $dataProvider = $testMethodModel->getDataProvider();
                            $dataSets = $testCase->$dataProvider();
                            foreach ($dataSets as $args) {
                                yield $this->invokeTest(
                                    $testSuite,
                                    $testCase,
                                    $assertionContext,
                                    $asyncAssertionContext,
                                    $testSuiteModel,
                                    $testCaseModel,
                                    $testMethodModel,
                                    $args
                                );
                                [$testCase, $assertionContext, $asyncAssertionContext] = $this->invokeTestCaseConstructor($testCaseModel->getClass(), $testSuite);
                            }
                        } else {
                            yield $this->invokeTest(
                                $testSuite,
                                $testCase,
                                $assertionContext,
                                $asyncAssertionContext,
                                $testSuiteModel,
                                $testCaseModel,
                                $testMethodModel
                            );
                        }
                    }

                    if (!$testCaseModel->isDisabled()) {
                        yield $this->invokeHooks($testCaseModel->getClass(), $testCaseModel, HookType::AfterAll(), TestCaseTearDownException::class, [$testSuite]);
                    }
                    if (!$testSuiteModel->isDisabled()) {
                        yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::AfterEach(), TestSuiteTearDownException::class);
                    }
                    yield $this->emitter->emit(new TestCaseFinishedEvent($testCaseModel));
                }

                if (!$testSuiteModel->isDisabled()) {
                    yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::AfterAll(), TestSuiteTearDownException::class);
                }
                yield $this->emitter->emit(new TestSuiteFinishedEvent($testSuiteModel));
            }
        });
    }

    private function invokeHooks(
        TestSuite|TestCase|string $hookTarget,
        TestSuiteModel|TestCaseModel $model,
        HookType $hookType,
        string $exceptionType,
        array $args = []
    ) : Promise {
        return call(function() use($hookTarget, $model, $hookType, $exceptionType, $args) {
            foreach ($model->getHooks($hookType) as $hookMethodModel) {
                try {
                    yield call([$hookTarget, $hookMethodModel->getMethod()], ...$args);
                } catch (Throwable $throwable) {
                    $hookTypeInflected = str_starts_with($hookType->toString(), 'Before') ? 'setting up' : 'tearing down';
                    $msg = sprintf(
                        'Failed %s "%s::%s" #[%s] hook with exception of type "%s" with code %d and message "%s".',
                        $hookTypeInflected,
                        is_string($hookTarget) ? $hookTarget : $hookTarget::class,
                        $hookMethodModel->getMethod(),
                        $hookType->toString(),
                        $throwable::class,
                        $throwable->getCode(),
                        $throwable->getMessage()
                    );
                    throw new $exceptionType($msg, previous: $throwable);
                }
            }
        });
    }

    private function invokeTest(
        TestSuite $testSuite,
        TestCase $testCase,
        AssertionContext $assertionContext,
        AsyncAssertionContext $asyncAssertionContext,
        TestSuiteModel $testSuiteModel,
        TestCaseModel $testCaseModel,
        TestModel $testMethodModel,
        array $args = []
    ) : Promise {
        return call(function() use($testSuite, $testCase, $assertionContext, $asyncAssertionContext, $testSuiteModel, $testCaseModel, $testMethodModel, $args) {
            if ($testMethodModel->isDisabled()) {
                $msg = $testMethodModel->getDisabledReason() ??
                    $testCaseModel->getDisabledReason() ??
                    $testSuiteModel->getDisabledReason() ??
                    sprintf('%s::%s has been marked disabled via annotation', $testCaseModel->getClass(), $testMethodModel->getMethod());
                $exception = new TestDisabledException($msg);
                $testResult = $this->getDisabledTestResult($testCase, $testMethodModel->getMethod(), $exception);
                yield $this->emitter->emit(new TestProcessedEvent($testResult));
                yield $this->emitter->emit(new TestDisabledEvent($testResult));
                return;
            }

            yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::BeforeEachTest(), TestSetupException::class);
            yield $this->invokeHooks($testCase, $testCaseModel, HookType::BeforeEach(), TestSetupException::class);

            $testCaseMethod = $testMethodModel->getMethod();
            $failureException = null;
            try {
                ob_start();
                yield call(fn() => $testCase->$testCaseMethod(...$args));
                if ($assertionContext->getAssertionCount() === 0 && $asyncAssertionContext->getAssertionCount() === 0) {
                    $msg = sprintf(
                        'Expected "%s::%s" #[Test] to make at least 1 Assertion but none were made.',
                        $testCase::class,
                        $testCaseMethod
                    );
                    throw new TestFailedException($msg);
                }
            } catch (TestFailedException $exception) {
                $failureException = $exception;
            } catch (Throwable $throwable) {
                $msg = sprintf(
                    'An unexpected exception of type "%s" with code %s and message "%s" was thrown from #[Test] %s::%s',
                    $throwable::class,
                    $throwable->getCode(),
                    $throwable->getMessage(),
                    $testCase::class,
                    $testMethodModel->getMethod()
                );
                $failureException = new TestFailedException($msg, previous: $throwable);
            } finally {
                $output = ob_get_clean();
                if ($output && !$failureException) {
                    $msg = sprintf(
                        'Test had unexpected output:%s%s"%s"',
                        PHP_EOL,
                        PHP_EOL,
                        $output
                    );
                    $failureException = new TestOutputException($msg);
                }
                $state = is_null($failureException) ? TestState::Passed() : TestState::Failed();
                $testResult = $this->getTestResult($testCase, $testCaseMethod, $state, $failureException);
            }

            yield $this->invokeHooks($testCase, $testCaseModel, HookType::AfterEach(), TestTearDownException::class);
            yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::AfterEachTest(), TestTearDownException::class);

            yield $this->emitter->emit(new TestProcessedEvent($testResult));

            if (TestState::Passed()->equals($testResult->getState())) {
                yield $this->emitter->emit(new TestPassedEvent($testResult));
            } else {
                yield $this->emitter->emit(new TestFailedEvent($testResult));
            }

            unset($testCase);
            unset($failureException);
            unset($testResult);
        });
    }

    private function getReflectionClass(string $class) : ReflectionClass {
        if (!isset($this->reflectionCache[$class])) {
            $this->reflectionCache[$class] = new ReflectionClass($class);
        }

        return $this->reflectionCache[$class];
    }

    private function getDisabledTestResult(TestCase $testCase, string $testMethod, TestDisabledException $exception) {
        return new class($testCase, $testMethod, $exception) implements TestResult {

            public function __construct(
                private TestCase $testCase,
                private string $testMethod,
                private TestDisabledException $exception
            ) {}

            public function getTestCase() : TestCase {
                return $this->testCase;
            }

            public function getTestMethod() : string {
                return $this->testMethod;
            }

            public function getState() : TestState {
                return TestState::Disabled();
            }

            public function getException() : TestFailedException|AssertionFailedException|TestDisabledException|null {
                return $this->exception;
            }
        };
    }

    private function getTestResult(
        TestCase $testCase,
        string $method,
        TestState $state,
        ?TestFailedException $testFailedException
    ) : TestResult {
        return new class($testCase, $method, $state, $testFailedException) implements TestResult {

            public function __construct(
                private TestCase $testCase,
                private string $method,
                private TestState $state,
                private ?TestFailedException $testFailedException
            ) {}

            public function getTestCase() : TestCase {
                return $this->testCase;
            }

            public function getTestMethod() : string {
                return $this->method;
            }

            public function getState() : TestState {
                return $this->state;
            }

            public function getException() : TestFailedException|AssertionFailedException|TestDisabledException|null {
                return $this->testFailedException;
            }
        };
    }

    private function invokeTestCaseConstructor(string $testCaseClass, TestSuite $testSuite) : array {
        /** @var TestCase $testCaseObject */
        $reflectionClass = $this->getReflectionClass($testCaseClass);
        $testCaseObject = $reflectionClass->newInstanceWithoutConstructor();
        $reflectedAssertionContext = $this->getReflectionClass(AssertionContext::class);
        $reflectedAsyncAssertionContext = $this->getReflectionClass(AsyncAssertionContext::class);
        $testCaseConstructor = $reflectionClass->getConstructor();
        $testCaseConstructor->setAccessible(true);

        $assertionContext = $reflectedAssertionContext->newInstanceWithoutConstructor();
        $assertionContextConstructor = $reflectedAssertionContext->getConstructor();
        $assertionContextConstructor->setAccessible(true);
        $assertionContextConstructor->invoke($assertionContext, $this->customAssertionContext);

        $asyncAssertionContext = $reflectedAsyncAssertionContext->newInstanceWithoutConstructor();
        $asyncAssertionContextConstructor = $reflectedAsyncAssertionContext->getConstructor();
        $asyncAssertionContextConstructor->setAccessible(true);
        $asyncAssertionContextConstructor->invoke($asyncAssertionContext, $this->customAssertionContext);

        $testCaseConstructor->invoke(
            $testCaseObject,
            $testSuite,
            $assertionContext,
            $asyncAssertionContext
        );
        return [$testCaseObject, $assertionContext, $asyncAssertionContext];
    }

}