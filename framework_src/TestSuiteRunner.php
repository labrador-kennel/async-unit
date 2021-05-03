<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Promise;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestSetupException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestTearDownException;
use Cspray\Labrador\AsyncUnit\Model\InvokedTestCaseTestModel;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestMethodModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use ReflectionClass;
use Throwable;
use function Amp\call;

/**
 * @internal
 */
final class TestSuiteRunner {

    private array $reflectionCache = [];

    public function __construct(private EventEmitter $emitter, private CustomAssertionContext $customAssertionContext) {}

    public function runTestSuites(TestSuiteModel... $testSuiteModels) : Promise {
        return call(function() use($testSuiteModels) {
            foreach ($testSuiteModels as $testSuiteModel) {
                $testSuiteClass = $testSuiteModel->getClass();
                $testSuite = new $testSuiteClass();

                foreach ($testSuiteModel->getBeforeAllMethodModels() as $beforeAllMethodModel) {
                    try {
                        yield call([$testSuite, $beforeAllMethodModel->getMethod()]);
                    } catch (Throwable $throwable) {
                        $msg = sprintf(
                            'Failed setting up "%s::%s" #[BeforeAll] hook with exception of type "%s" with code %d and message "%s".',
                            $testSuiteClass,
                            $beforeAllMethodModel->getMethod(),
                            $throwable::class,
                            $throwable->getCode(),
                            $throwable->getMessage()
                        );
                        throw new TestSuiteSetUpException($msg, previous: $throwable);
                    }
                }

                foreach ($testSuiteModel->getTestCaseModels() as $testCaseModel) {
                    $testCaseClass = $testCaseModel->getClass();

                    foreach ($testSuiteModel->getBeforeEachMethodModels() as $beforeEachMethodModel) {
                        try {
                            yield call([$testSuite, $beforeEachMethodModel->getMethod()]);
                        } catch (Throwable $throwable) {
                            $msg = sprintf(
                                'Failed setting up "%s::%s" #[BeforeEach] hook with exception of type "%s" with code %d and message "%s".',
                                $testSuiteClass,
                                $beforeEachMethodModel->getMethod(),
                                $throwable::class,
                                $throwable->getCode(),
                                $throwable->getMessage()
                            );
                            throw new TestSuiteSetUpException($msg, previous: $throwable);
                        }
                    }

                    foreach ($testCaseModel->getBeforeAllMethodModels() as $beforeAllMethodModel) {
                        try {
                            yield call([$testCaseClass, $beforeAllMethodModel->getMethod()]);
                        } catch (Throwable $throwable) {
                            $msg = sprintf(
                                'Failed setting up "%s::%s" #[BeforeAll] hook with exception of type "%s" with code %d and message "%s".',
                                $testCaseClass,
                                $beforeAllMethodModel->getMethod(),
                                $throwable::class,
                                $throwable->getCode(),
                                $throwable->getMessage()
                            );
                            throw new TestCaseSetUpException($msg, previous: $throwable);
                        }
                    }

                    foreach ($testCaseModel->getTestMethodModels() as $testMethodModel) {
                        /** @var AssertionContext $assertionContext */
                        /** @var AsyncAssertionContext $asyncAssertionContext */
                        [$testCaseObject, $assertionContext, $asyncAssertionContext] = $this->invokeTestCaseConstructor($testCaseClass, $testSuite);
                        if ($testMethodModel->getDataProvider() !== null) {
                            $dataProvider = $testMethodModel->getDataProvider();
                            $dataSets = $testCaseObject->$dataProvider();
                            foreach ($dataSets as $args) {
                                yield $this->invokeTest(
                                    $testSuite,
                                    $testCaseObject,
                                    $assertionContext,
                                    $asyncAssertionContext,
                                    $testSuiteModel,
                                    $testCaseModel,
                                    $testMethodModel,
                                    $args
                                );
                                [$testCaseObject, $assertionContext, $asyncAssertionContext] = $this->invokeTestCaseConstructor($testCaseClass, $testSuite);
                            }
                        } else {
                            yield $this->invokeTest(
                                $testSuite,
                                $testCaseObject,
                                $assertionContext,
                                $asyncAssertionContext,
                                $testSuiteModel,
                                $testCaseModel,
                                $testMethodModel
                            );
                        }
                    }

                    foreach ($testCaseModel->getAfterAllMethodModels() as $afterAllMethodModel) {
                        try {
                            yield call([$testCaseClass, $afterAllMethodModel->getMethod()]);
                        } catch (Throwable $throwable) {
                            $msg = sprintf(
                                'Failed tearing down "%s::%s" #[AfterAll] hook with exception of type "%s" with code %d and message "%s".',
                                $testCaseClass,
                                $afterAllMethodModel->getMethod(),
                                $throwable::class,
                                $throwable->getCode(),
                                $throwable->getMessage()
                            );
                            throw new TestCaseTearDownException($msg, previous: $throwable);
                        }
                    }

                    foreach ($testSuiteModel->getAfterEachMethodModels() as $afterEachMethodModel) {
                        try {
                            yield call([$testSuite, $afterEachMethodModel->getMethod()]);
                        } catch (Throwable $throwable) {
                            $msg = sprintf(
                                'Failed tearing down "%s::%s" #[AfterEach] hook with exception of type "%s" with code %d and message "%s".',
                                $testSuiteClass,
                                $afterEachMethodModel->getMethod(),
                                $throwable::class,
                                $throwable->getCode(),
                                $throwable->getMessage()
                            );
                            throw new TestSuiteTearDownException($msg, previous: $throwable);
                        }
                    }
                }

                foreach ($testSuiteModel->getAfterAllMethodModels() as $afterAllMethodModel) {
                    try {
                        yield call([$testSuite, $afterAllMethodModel->getMethod()]);
                    } catch (Throwable $throwable) {
                        $msg = sprintf(
                            'Failed tearing down "%s::%s" #[AfterAll] hook with exception of type "%s" with code %d and message "%s".',
                            $testSuiteClass,
                            $afterAllMethodModel->getMethod(),
                            $throwable::class,
                            $throwable->getCode(),
                            $throwable->getMessage()
                        );
                        throw new TestSuiteTearDownException($msg, previous: $throwable);
                    }
                }
            }
        });
        // yield the instantiated object and name of invoked method
    }

    private function invokeTest(
        TestSuite $testSuite,
        TestCase $testCase,
        AssertionContext $assertionContext,
        AsyncAssertionContext $asyncAssertionContext,
        TestSuiteModel $testSuiteModel,
        TestCaseModel $testCaseModel,
        TestMethodModel $testMethodModel,
        array $args = []
    ) : Promise {
        return call(function() use($testSuite, $testCase, $assertionContext, $asyncAssertionContext, $testSuiteModel, $testCaseModel, $testMethodModel, $args) {
            foreach ($testSuiteModel->getBeforeEachTestMethodModels() as $hook) {
                try {
                    yield call([$testSuite, $hook->getMethod()]);
                } catch (Throwable $throwable) {
                    $msg = sprintf(
                        'Failed setting up "%s::%s" #[BeforeEachTest] hook with exception of type "%s" with code %d and message "%s".',
                        $testSuite::class,
                        $hook->getMethod(),
                        $throwable::class,
                        $throwable->getCode(),
                        $throwable->getMessage()
                    );
                    throw new TestSetupException($msg, previous: $throwable);
                }
            }

            foreach ($testCaseModel->getBeforeEachMethodModels() as $beforeEachMethodModel) {
                try {
                    yield call([$testCase, $beforeEachMethodModel->getMethod()]);
                } catch (Throwable $throwable) {
                    $msg = sprintf(
                        'Failed setting up "%s::%s" #[BeforeEach] hook with exception of type "%s" with code %d and message "%s".',
                        $testCase::class,
                        $beforeEachMethodModel->getMethod(),
                        $throwable::class,
                        $throwable->getCode(),
                        $throwable->getMessage()
                    );
                    throw new TestSetupException($msg, previous: $throwable);
                }
            }

            $testCaseMethod = $testMethodModel->getMethod();
            $failureException = null;
            try {
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
                $invokedModel = new InvokedTestCaseTestModel(
                    $testSuite,
                    $testCase,
                    $testMethodModel->getMethod(),
                    $assertionContext->getAssertionCount(),
                    $asyncAssertionContext->getAssertionCount(),
                    $failureException
                );
            }

            foreach ($testCaseModel->getAfterEachMethodModels() as $afterEachMethodModel) {
                try {
                    yield call([$testCase, $afterEachMethodModel->getMethod()]);
                } catch (Throwable $throwable) {
                    $msg = sprintf(
                        'Failed tearing down "%s::%s" #[AfterEach] hook with exception of type "%s" with code %d and message "%s".',
                        $testCase::class,
                        $afterEachMethodModel->getMethod(),
                        $throwable::class,
                        $throwable->getCode(),
                        $throwable->getMessage()
                    );
                    throw new TestTearDownException($msg, previous: $throwable);
                }
            }

            foreach ($testSuiteModel->getAfterEachTestMethodModels() as $hook) {
                try {
                    yield call([$testSuite, $hook->getMethod()]);
                } catch (Throwable $throwable) {
                    throw new TestTearDownException(sprintf(
                        'Failed tearing down "%s::%s" #[AfterEachTest] hook with exception of type "%s" with code %d and message "%s".',
                        $testSuite::class,
                        $hook->getMethod(),
                        $throwable::class,
                        $throwable->getCode(),
                        $throwable->getMessage()
                    ));
                }
            }

            yield $this->emitter->emit(new TestInvokedEvent($invokedModel));

            unset($testCase);
            unset($failureException);
        });
    }

    private function getReflectionClass(string $class) : ReflectionClass {
        if (!isset($this->reflectionCache[$class])) {
            $this->reflectionCache[$class] = new ReflectionClass($class);
        }

        return $this->reflectionCache[$class];
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