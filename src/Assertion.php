<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

use Amp\Promise;
use Generator;

interface Assertion {

    public function assert(mixed $value, string $errorMessage = '') : AssertionResult;

}