<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal;

use Amp\Promise;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestSetupException;
use Cspray\Labrador\AsyncUnit\Exception\TestTearDownException;
use Cspray\Labrador\AsyncUnit\Internal\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncUnit\Internal\Model\InvokedTestCaseTestModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\TestCase;
use ReflectionClass;
use Throwable;
use function Amp\call;

/**
 * @internal
 */
class TestSuiteRunner {

    private array $reflectionCache = [];

    public function __construct(private EventEmitter $emitter) {}

    public function runTestSuites(TestSuiteModel... $testSuiteModels) : Promise {
        return call(function() use($testSuiteModels) {
            foreach ($testSuiteModels as $testSuiteModel) {
                foreach ($testSuiteModel->getTestCaseModels() as $testCaseModel) {
                    $testCaseClass = $testCaseModel->getTestCaseClass();
                    foreach ($testCaseModel->getBeforeAllMethodModels() as $beforeAllMethodModel) {
                        try {
                            yield call([$testCaseClass, $beforeAllMethodModel->getMethod()]);
                        } catch (Throwable $throwable) {
                            $msg = sprintf(
                                'Failed setting up "%s::%s" #[BeforeAll] hook with exception of type "%s" with code %s and message "%s".',
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
                        [$testCaseObject, $assertionContext, $asyncAssertionContext] = $this->invokeTestCaseConstructor($testCaseClass);
                        foreach ($testCaseModel->getBeforeEachMethodModels() as $beforeEachMethodModel) {
                            try {
                                yield call([$testCaseObject, $beforeEachMethodModel->getMethod()]);
                            } catch (Throwable $throwable) {
                                $msg = sprintf(
                                    'Failed setting up "%s::%s" #[BeforeEach] hook with exception of type "%s" with code %s and message "%s".',
                                    $testCaseClass,
                                    $beforeEachMethodModel->getMethod(),
                                    $throwable::class,
                                    $throwable->getCode(),
                                    $throwable->getMessage()
                                );
                                throw new TestSetupException($msg);
                            }
                        }

                        $testCaseMethod = $testMethodModel->getMethod();
                        $failureException = null;
                        try {
                            yield call(fn() => $testCaseObject->$testCaseMethod());
                            if ($assertionContext->getAssertionCount() === 0 && $asyncAssertionContext->getAssertionCount() === 0) {
                                $msg = sprintf(
                                    'Expected "%s::%s" #[Test] to make at least 1 Assertion but none were made.',
                                    $testCaseClass,
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
                                $testCaseObject::class,
                                $testMethodModel->getMethod()
                            );
                            $failureException = new TestFailedException($msg, previous: $throwable);
                        } finally {
                            $invokedModel = new InvokedTestCaseTestModel(
                                $testCaseObject,
                                $testMethodModel->getMethod(),
                                $failureException
                            );
                        }

                        foreach ($testCaseModel->getAfterEachMethodModels() as $afterEachMethodModel) {
                            try {
                                yield call([$testCaseObject, $afterEachMethodModel->getMethod()]);
                            } catch (Throwable $throwable) {
                                $msg = sprintf(
                                    'Failed tearing down "%s::%s" #[AfterEach] hook with exception of type "%s" with code %s and message "%s".',
                                    $testCaseClass,
                                    $afterEachMethodModel->getMethod(),
                                    $throwable::class,
                                    $throwable->getCode(),
                                    $throwable->getMessage()
                                );
                                throw new TestTearDownException($msg);
                            }
                        }

                        yield $this->emitter->emit(new TestInvokedEvent($invokedModel));

                        unset($testCaseObject);
                        unset($failureException);
                    }

                    foreach ($testCaseModel->getAfterAllMethodModels() as $afterAllMethodModel) {
                        try {
                            yield call([$testCaseClass, $afterAllMethodModel->getMethod()]);
                        } catch (Throwable $throwable) {
                            $msg = sprintf(
                                'Failed tearing down "%s::%s" #[AfterAll] hook with exception of type "%s" with code %s and message "%s".',
                                $testCaseClass,
                                $afterAllMethodModel->getMethod(),
                                $throwable::class,
                                $throwable->getCode(),
                                $throwable->getMessage()
                            );
                            throw new TestCaseTearDownException($msg, previous: $throwable);
                        }
                    }
                }
            }
        });
        // yield the instantiated object and name of invoked method
    }

    private function getReflectionClass(string $class) : ReflectionClass {
        if (!isset($this->reflectionCache[$class])) {
            $this->reflectionCache[$class] = new ReflectionClass($class);
        }

        return $this->reflectionCache[$class];
    }

    private function invokeTestCaseConstructor(string $testCaseClass) : array {
        /** @var TestCase $testCaseObject */
        $reflectionClass = $this->getReflectionClass($testCaseClass);
        $testCaseObject = $reflectionClass->newInstanceWithoutConstructor();
        $reflectedAssertionContext = $this->getReflectionClass(AssertionContext::class);
        $reflectedAsyncAssertionContext = $this->getReflectionClass(AsyncAssertionContext::class);
        $testCaseConstructor = $reflectionClass->getConstructor();
        $testCaseConstructor->setAccessible(true);
        $assertionContext = $reflectedAssertionContext->newInstanceWithoutConstructor();
        $asyncAssertionContext = $reflectedAsyncAssertionContext->newInstanceWithoutConstructor();
        $testCaseConstructor->invoke(
            $testCaseObject,
            $assertionContext,
            $asyncAssertionContext
        );
        return [$testCaseObject, $assertionContext, $asyncAssertionContext];
    }

}