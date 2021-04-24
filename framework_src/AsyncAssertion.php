<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit;


use Amp\Coroutine;
use Amp\Promise;
use Generator;

interface AsyncAssertion {

    /**
     * @param Promise|Generator|Coroutine $actual
     * @return Promise<AssertionResult>
     */
    public function assert(Promise|Generator|Coroutine $actual) : Promise;

}