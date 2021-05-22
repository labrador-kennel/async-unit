<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Prototype;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Generator;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(Test::class)]
interface TestPrototype {

    public function test() : Promise|Generator|Coroutine|null;

}