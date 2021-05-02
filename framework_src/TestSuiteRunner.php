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
                /** @var TestSuite $testSuite */
                $testSuite = (new ReflectionClass($testSuiteClass))->newInstanceWithoutConstructor();

                yield $this->invokeHooks($testSuite, $testSuiteModel, 'BeforeAll', TestSuiteSetUpException::class);

                foreach ($testSuiteModel->getTestCaseModels() as $testCaseModel) {

                    yield $this->invokeHooks($testSuite, $testSuiteModel, 'BeforeEach', TestSuiteSetUpException::class);
                    yield $this->invokeHooks($testCaseModel->getClass(), $testCaseModel, 'BeforeAll', TestCaseSetUpException::class, [$testSuite]);

                    foreach ($testCaseModel->getTestMethodModels() as $testMethodModel) {
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

                    yield $this->invokeHooks($testCaseModel->getClass(), $testCaseModel, 'AfterAll', TestCaseTearDownException::class, [$testSuite]);
                    yield $this->invokeHooks($testSuite, $testSuiteModel, 'AfterEach', TestSuiteTearDownException::class);
                }

                yield $this->invokeHooks($testSuite, $testSuiteModel, 'AfterAll', TestSuiteTearDownException::class);
            }
        });
    }

    private function invokeHooks(
        TestSuite|TestCase|string $hookTarget,
        TestSuiteModel|TestCaseModel $model,
        string $hookType,
        string $exceptionType,
        array $args = []
    ) : Promise {
        return call(function() use($hookTarget, $model, $hookType, $exceptionType, $args) {
            foreach ($model->getHooks($hookType) as $hookMethodModel) {
                try {
                    yield call([$hookTarget, $hookMethodModel->getMethod()], ...$args);
                } catch (Throwable $throwable) {
                    $hookTypeInflected = str_starts_with($hookType, 'Before') ? 'setting up' : 'tearing down';
                    $msg = sprintf(
                        'Failed %s "%s::%s" #[%s] hook with exception of type "%s" with code %d and message "%s".',
                        $hookTypeInflected,
                        is_string($hookTarget) ? $hookTarget : $hookTarget::class,
                        $hookMethodModel->getMethod(),
                        $hookType,
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
        TestMethodModel $testMethodModel,
        array $args = []
    ) : Promise {
        return call(function() use($testSuite, $testCase, $assertionContext, $asyncAssertionContext, $testSuiteModel, $testCaseModel, $testMethodModel, $args) {
            yield $this->invokeHooks($testSuite, $testSuiteModel, 'BeforeEachTest', TestSetupException::class);
            yield $this->invokeHooks($testCase, $testCaseModel, 'BeforeEach', TestSetupException::class);

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
                    $testCase,
                    $testMethodModel->getMethod(),
                    $assertionContext->getAssertionCount(),
                    $asyncAssertionContext->getAssertionCount(),
                    $failureException
                );
            }

            yield $this->invokeHooks($testCase, $testCaseModel, 'AfterEach', TestTearDownException::class);
            yield $this->invokeHooks($testSuite, $testSuiteModel, 'AfterEachTest', TestTearDownException::class);

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