<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;


use Cspray\Labrador\AsyncUnit\Assertion;

class AsyncAssertIsFalse extends AbstractAsyncAssertion {

    protected function getAssertion() : Assertion {
        return new AssertIsFalse();
    }

}