<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

interface TestMocker {

    public function createMock(string $class) : object;

}