<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

interface TestExpector {

    public function exception(string $type) : void;

    public function exceptionMessage(string $message) : void;

}