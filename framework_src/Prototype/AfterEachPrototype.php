<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Prototype;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Prototype([TestSuite::class, TestCase::class])]
#[PrototypeRequiresAttribute(AfterEach::class)]
interface AfterEachPrototype {

    public function afterEach() : Promise|Generator|Coroutine|null;

}