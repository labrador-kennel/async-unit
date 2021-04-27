<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Delayed;
use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Stub\AssertNotTestCase;
use Cspray\Labrador\AsyncUnit\Stub\CustomAssertionTestCase;
use Cspray\Labrador\AsyncUnit\Stub\FailingTestCase;
use function Amp\call;

/**
 * @covers \Cspray\Labrador\AsyncUnit\TestCase
 */
class TestCaseTest extends \PHPUnit\Framework\TestCase {

    public function testFailingAssertionHasFileAndLine() {
        [$subject] = $this->getSubjectAndContexts(FailingTestCase::class);
        $assertionException = null;
        try {
            $subject->doFailure();
        } catch (AssertionFailedException $exception) {
            $assertionException = $exception;
        } finally {
            $this->assertNotNull($assertionException);
            $this->assertSame('Failed comparing that 2 strings are equal to one another', $assertionException->getMessage());
            $this->assertSame(__DIR__ . '/Stub/FailingTestCase.php', $assertionException->getAssertionFailureFile());
            $this->assertSame(11, $assertionException->getAssertionFailureLine());
            $this->assertEquals(
                sprintf('Failed %s', (new BinaryVarExportAssertionComparisonDisplay('foo', 'bar'))->toString()),
                $assertionException->getDetailedMessage()
            );
        }
    }

    public function testFailingAssertionHasCustomMessage() {
        [$subject] = $this->getSubjectAndContexts(FailingTestCase::class);
        $assertionException = null;
        try {
            $subject->doFailureWithCustomMessage();
        } catch (AssertionFailedException $exception) {
            $assertionException = $exception;
        } finally {
            $this->assertNotNull($assertionException);
            $this->assertSame('my custom message', $assertionException->getMessage());
            $this->assertSame(__DIR__ . '/Stub/FailingTestCase.php', $assertionException->getAssertionFailureFile());
            $this->assertSame(19, $assertionException->getAssertionFailureLine());
            $this->assertEquals(
                sprintf('Failed %s', (new BinaryVarExportAssertionComparisonDisplay('foo', 'bar'))->toString()),
                $assertionException->getDetailedMessage()
            );
        }
    }

    public function testFailingAsyncAssertionHasFileAndLine() {
        Loop::run(function() {
            [$subject] = $this->getSubjectAndContexts(FailingTestCase::class);

            $assertionException = null;
            try {
                yield call([$subject, 'doAsyncFailure']);
            } catch (AssertionFailedException $exception) {
                $assertionException = $exception;
            } finally {
                $this->assertNotNull($assertionException);
                $this->assertSame('Failed comparing that 2 strings are equal to one another', $assertionException->getMessage());
                $this->assertSame(__DIR__ . '/Stub/FailingTestCase.php', $assertionException->getAssertionFailureFile());
                $this->assertSame(15, $assertionException->getAssertionFailureLine());
                $this->assertEquals(
                    sprintf('Failed %s', (new BinaryVarExportAssertionComparisonDisplay('foo', 'bar'))->toString()),
                    $assertionException->getDetailedMessage()
                );
            }
        });
    }

    public function testRunningOnlyNotAssertionPassing() {
        Loop::run(function() {
            /** @var AssertNotTestCase $subject */
            /** @var AssertionContext $assertionContext */
            /** @var AsyncAssertionContext $asyncAssertionContext */
            [$subject, $assertionContext, $asyncAssertionContext] = $this->getSubjectAndContexts(AssertNotTestCase::class);

            $subject->doNotAssertion();
            $this->assertEquals(1, $assertionContext->getAssertionCount());

            yield call(fn() => $subject->doAsyncNotAssertion());
            $this->assertEquals(1, $asyncAssertionContext->getAssertionCount());
        });
    }

    public function testRunningOnlyNotAssertionFailing() {
        Loop::run(function() {
            /** @var AssertNotTestCase $subject */
            [$subject] = $this->getSubjectAndContexts(AssertNotTestCase::class);

            $assertionException = null;
            try{
                $subject->doFailingNotAssertions();
            } catch (AssertionFailedException $exception) {
                $assertionException = $exception;
            } finally {
                $this->assertNotNull($assertionException);
                $this->assertSame('Failed comparing that 2 strings are not equal to one another', $assertionException->getMessage());
                $this->assertSame(__DIR__ . '/Stub/AssertNotTestCase.php', $assertionException->getAssertionFailureFile());
                $this->assertSame(15, $assertionException->getAssertionFailureLine());
            }
        });
    }

    public function testRunningBothNotAndRegularAssertionPassing() {
        Loop::run(function() {
            /** @var AssertNotTestCase $subject */
            /** @var AssertionContext $assertionContext */
            [$subject, $assertionContext] = $this->getSubjectAndContexts(AssertNotTestCase::class);

            $subject->doBothAssertions();

            $this->assertSame(3, $assertionContext->getAssertionCount());
        });
    }

    public function testRunningCustomAssertions() {
        Loop::run(function() {
            /** @var CustomAssertionTestCase $subject */
            /** @var AssertionContext $assertionContext */
            /** @var CustomAssertionContext $customAssertionContext */
            [$subject, $assertionContext, $_, $customAssertionContext] = $this->getSubjectAndContexts(CustomAssertionTestCase::class);

            $assertion = $this->getMockBuilder(Assertion::class)->getMock();
            $assertResult = $this->getMockBuilder(AssertionResult::class)->getMock();
            $assertResult->expects($this->once())->method('isSuccessful')->willReturn(true);
            $assertion->expects($this->once())->method('assert')->willReturn($assertResult);
            $state = new \stdClass();
            $state->args = null;
            $customAssertionContext->registerAssertion('myCustomAssertion', function(...$args) use($assertion, $state) {
                $state->args = $args;
                return $assertion;
            });

            $subject->doCustomAssertion();

            $this->assertSame(1, $assertionContext->getAssertionCount());
            $this->assertSame([1,2,3], $state->args);
        });
    }

    public function testRunningCustomAsyncAssertions() {
        Loop::run(function() {
            /** @var CustomAssertionTestCase $subject */
            /** @var AsyncAssertionContext $asyncAssertionContext */
            /** @var CustomAssertionContext $customAssertionContext */
            [$subject, $_, $asyncAssertionContext, $customAssertionContext] = $this->getSubjectAndContexts(CustomAssertionTestCase::class);

            $assertion = $this->getMockBuilder(AsyncAssertion::class)->getMock();
            $assertResult = $this->getMockBuilder(AssertionResult::class)->getMock();
            $assertResult->expects($this->once())->method('isSuccessful')->willReturn(true);
            $assertion->expects($this->once())->method('assert')->willReturn(new Success($assertResult));
            $state = new \stdClass();
            $state->args = null;
            $customAssertionContext->registerAsyncAssertion('myCustomAssertion', function(...$args) use($assertion, $state) {
                $state->args = $args;
                return $assertion;
            });

            yield call(fn() => $subject->doCustomAsyncAssertion());

            $this->assertSame(1, $asyncAssertionContext->getAssertionCount());
            $this->assertSame([1,2,3], $state->args);
        });
    }

    public function getSubjectAndContexts(string $testCase) {
        $customAssertionContext = (new \ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $reflectedAssertionContext = new \ReflectionClass(AssertionContext::class);
        $assertionContext = $reflectedAssertionContext->newInstanceWithoutConstructor();
        $assertionContextConstructor = $reflectedAssertionContext->getConstructor();
        $assertionContextConstructor->setAccessible(true);
        $assertionContextConstructor->invoke($assertionContext, $customAssertionContext);

        $reflectedAsyncAssertionContext = new \ReflectionClass(AsyncAssertionContext::class);
        $asyncAssertionContext = $reflectedAsyncAssertionContext->newInstanceWithoutConstructor();
        $asyncAssertionContextConstructor = $reflectedAsyncAssertionContext->getConstructor();
        $asyncAssertionContextConstructor->setAccessible(true);
        $asyncAssertionContextConstructor->invoke($asyncAssertionContext, $customAssertionContext);

        $reflectedSubject = new \ReflectionClass($testCase);
        $constructor = $reflectedSubject->getConstructor();
        $constructor->setAccessible(true);
        $subject = $reflectedSubject->newInstanceWithoutConstructor();
        $constructor->invoke($subject, $assertionContext, $asyncAssertionContext);

        return [$subject, $assertionContext, $asyncAssertionContext, $customAssertionContext];
    }
}