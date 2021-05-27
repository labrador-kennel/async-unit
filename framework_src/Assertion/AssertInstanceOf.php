<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionResult;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;

final class AssertInstanceOf implements Assertion {

    private string|object $expected;
    private object $actual;

    public function __construct(string|object $expected, object $actual) {
        if (is_string($expected) && !class_exists($expected) && !interface_exists($expected)) {
            $msg = sprintf(
                'The expected value must be a valid class but %s was given',
                var_export($expected, true)
            );
            throw new InvalidArgumentException($msg);
        }
        $this->expected = $expected;
        $this->actual = $actual;
    }

    public function assert() : AssertionResult {
        $message = new Assertion\AssertionMessage\InstanceOfMessage($this->expected, $this->actual);
        if ($this->actual instanceof $this->expected) {
            return AssertionResultFactory::validAssertion($message, $message);
        }

        return AssertionResultFactory::invalidAssertion($message, $message);
    }
}