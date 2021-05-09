<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestOutputException;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use Throwable;
use function Amp\call;

final class ExpectationContext {

    private string $actualOutput = '';

    private ?Throwable $thrownException = null;

    private ?string $expectedExceptionClass = null;

    private ?string $expectedExceptionMessage = null;

    private function __construct(
        private TestModel $testModel,
        private AssertionContext $assertionContext,
        private AsyncAssertionContext $asyncAssertionContext
    ) {}

    public function setActualOutput(string $output) : void {
        $this->actualOutput = $output;
    }

    public function setThrownException(Throwable $throwable) : void {
        $this->thrownException = $throwable;
    }

    public function setExpectedExceptionClass(string $exceptionClass) : void {
        $this->expectedExceptionClass = $exceptionClass;
    }

    public function setExpectedExceptionMessage(string $exceptionMessage) : void {
        $this->expectedExceptionMessage = $exceptionMessage;
    }

    public function validateExpectations() : Promise {
        return call(function() {
            return $this->validateThrownException() ??
                $this->validateAssertionCount() ??
                $this->validateOutput() ??
                null;
        });
    }

    private function validateAssertionCount() : ?TestFailedException {
        if (is_null($this->expectedExceptionClass) && $this->assertionContext->getAssertionCount() === 0 && $this->asyncAssertionContext->getAssertionCount() === 0) {
            $msg = sprintf(
                'Expected "%s::%s" #[Test] to make at least 1 Assertion but none were made.',
                $this->testModel->getClass(),
                $this->testModel->getMethod()
            );
            return new TestFailedException($msg);
        }

        return null;
    }

    private function validateThrownException() : ?TestFailedException {
        if (isset($this->thrownException) && is_null($this->expectedExceptionClass)) {
            $msg = sprintf(
                'An unexpected exception of type "%s" with code %s and message "%s" was thrown from #[Test] %s::%s',
                $this->thrownException::class,
                $this->thrownException->getCode(),
                $this->thrownException->getMessage(),
                $this->testModel->getClass(),
                $this->testModel->getMethod()
            );
            return new TestFailedException($msg, previous: $this->thrownException);
        } else if (is_null($this->thrownException) && isset($this->expectedExceptionClass)) {
            $msg = sprintf(
                'Failed asserting that an exception of type %s is thrown',
                $this->expectedExceptionClass
            );
            return new TestFailedException($msg);
        } else if (isset($this->thrownException) && !$this->thrownException instanceof $this->expectedExceptionClass) {
            $msg = sprintf(
                'Failed asserting that thrown exception %s extends expected %s',
                $this->thrownException::class,
                $this->expectedExceptionClass
            );
            return new TestFailedException($msg);
        } else if (isset($this->thrownException) && isset($this->expectedExceptionMessage) && $this->thrownException->getMessage() !== $this->expectedExceptionMessage) {
            $msg = sprintf(
                'Failed asserting that thrown exception message "%s" equals expected "%s"',
                $this->thrownException->getMessage(),
                $this->expectedExceptionMessage
            );
            return new TestFailedException($msg);
        }

        return null;
    }

    private function validateOutput() : ?TestOutputException {
        if (!empty($this->actualOutput)) {
            $msg = sprintf(
                'Test had unexpected output:%s%s"%s"',
                PHP_EOL,
                PHP_EOL,
                $this->actualOutput
            );
            return new TestOutputException($msg);
        }

        return null;
    }

}