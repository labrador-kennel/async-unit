<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit;


use Amp\Coroutine;
use Amp\Promise;
use Generator;

interface AsyncAssertion {

    /**
     * @return Promise<AssertionResult>
     */
    public function assert() : Promise;

}