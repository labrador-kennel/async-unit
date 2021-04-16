<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Assertion;

use Cspray\Labrador\AsyncTesting\Assertion;
use Cspray\Labrador\AsyncTesting\AssertionResult;
use Cspray\Labrador\AsyncTesting\AssertionResultFactory;

class AssertStringEquals implements Assertion {

    public function __construct(private string $comparison) {}

    public function assert(mixed $value, string $errorMessage = null) : AssertionResult {
        $errorMessage = $errorMessage ?? 'Failed asserting that a value with type "int" is comparable to type "string".';
        return AssertionResultFactory::invalidAssertion($errorMessage);
    }
}