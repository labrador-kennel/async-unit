<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Exception;

class TestFailedException extends Exception {

    public function isAssertionFailure() : bool {
        return false;
    }

}