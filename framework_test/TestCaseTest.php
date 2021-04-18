<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Stub\FailingTestCase;
use function Amp\call;

/**
 * @covers \Cspray\Labrador\AsyncUnit\TestCase
 */
class TestCaseTest extends \PHPUnit\Framework\TestCase {

    public function testFailingAssertionHasFileAndLine() {
        $assertionContext = (new \ReflectionClass(AssertionContext::class))->newInstanceWithoutConstructor();
        $asyncAssertionContext = (new \ReflectionClass(AsyncAssertionContext::class))->newInstanceWithoutConstructor();
        $reflectedSubject = new \ReflectionClass(FailingTestCase::class);
        $constructor = $reflectedSubject->getConstructor();
        $constructor->setAccessible(true);
        $subject = $reflectedSubject->newInstanceWithoutConstructor();
        $constructor->invoke($subject, $assertionContext, $asyncAssertionContext);

        $assertionException = null;
        try {
            $subject->doFailure();
        } catch (AssertionFailedException $exception) {
            $assertionException = $exception;
        } finally {
            $this->assertNotNull($assertionException);
            $this->assertSame(__DIR__ . '/Stub/FailingTestCase.php', $assertionException->getAssertionFailureFile());
            $this->assertSame(11, $assertionException->getAssertionFailureLine());
        }
    }

    public function testFailingAsyncAssertionHasFileAndLine() {
        Loop::run(function() {
            $assertionContext = (new \ReflectionClass(AssertionContext::class))->newInstanceWithoutConstructor();
            $asyncAssertionContext = (new \ReflectionClass(AsyncAssertionContext::class))->newInstanceWithoutConstructor();
            $reflectedSubject = new \ReflectionClass(FailingTestCase::class);
            $constructor = $reflectedSubject->getConstructor();
            $constructor->setAccessible(true);
            $subject = $reflectedSubject->newInstanceWithoutConstructor();
            $constructor->invoke($subject, $assertionContext, $asyncAssertionContext);

            $assertionException = null;
            try {
                yield call([$subject, 'doAsyncFailure']);
            } catch (AssertionFailedException $exception) {
                $assertionException = $exception;
            } finally {
                $this->assertNotNull($assertionException);
                $this->assertSame(__DIR__ . '/Stub/FailingTestCase.php', $assertionException->getAssertionFailureFile());
                $this->assertSame(15, $assertionException->getAssertionFailureLine());
            }
        });
    }
}