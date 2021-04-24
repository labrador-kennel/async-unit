<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Stub\AssertNotTestCase;
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
                (new BinaryVarExportAssertionComparisonDisplay('foo', 'bar'))->toString(),
                $assertionException->getComparisonDisplay()->toString()
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
                (new BinaryVarExportAssertionComparisonDisplay('foo', 'bar'))->toString(),
                $assertionException->getComparisonDisplay()->toString()
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
                    (new BinaryVarExportAssertionComparisonDisplay('foo', 'bar'))->toString(),
                    $assertionException->getComparisonDisplay()->toString()
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

    public function getSubjectAndContexts(string $testCase) {
        $assertionContext = (new \ReflectionClass(AssertionContext::class))->newInstanceWithoutConstructor();
        $asyncAssertionContext = (new \ReflectionClass(AsyncAssertionContext::class))->newInstanceWithoutConstructor();
        $reflectedSubject = new \ReflectionClass($testCase);
        $constructor = $reflectedSubject->getConstructor();
        $constructor->setAccessible(true);
        $subject = $reflectedSubject->newInstanceWithoutConstructor();
        $constructor->invoke($subject, $assertionContext, $asyncAssertionContext);

        return [$subject, $assertionContext, $asyncAssertionContext];
    }
}