<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Prototype;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\DataProvider;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Generator;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(DataProvider::class)]
#[PrototypeRequiresAttribute(Test::class)]
interface DataProviderTestPrototype {

    public function test(mixed ...$args) : Promise|Generator|Coroutine|null;

}