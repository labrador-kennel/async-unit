<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestOutputException;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use function Amp\call;

final class ExpectationContext {

    private string $actualOutput = '';

    private function __construct(
        private TestModel $testModel,
        private AssertionContext $assertionContext,
        private AsyncAssertionContext $asyncAssertionContext
    ) {}

    public function setActualOutput(string $output) : void {
        $this->actualOutput = $output;
    }

    public function validateExpectations() : Promise {
        return call(function() {
            return $this->validateAssertionCount() ?? $this->validateOutput() ?? null;
        });
    }

    private function validateAssertionCount() : ?TestFailedException {
        if ($this->assertionContext->getAssertionCount() === 0 && $this->asyncAssertionContext->getAssertionCount() === 0) {
            $msg = sprintf(
                'Expected "%s::%s" #[Test] to make at least 1 Assertion but none were made.',
                $this->testModel->getClass(),
                $this->testModel->getMethod()
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