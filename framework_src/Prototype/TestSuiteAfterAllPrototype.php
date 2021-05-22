<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Prototype;


use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Prototype([TestSuite::class])]
#[PrototypeRequiresAttribute(AfterAll::class)]
interface TestSuiteAfterAllPrototype {

    public function afterAll() : Promise|Generator|Coroutine|null;

}