<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;
use function Amp\call;

class AsyncAssertStringEquals extends AbstractAsyncAssertion implements AsyncAssertion {

    public function __construct(private string $expected) {}

    protected function getAssertion() : Assertion {
        return new AssertStringEquals($this->expected);
    }
}