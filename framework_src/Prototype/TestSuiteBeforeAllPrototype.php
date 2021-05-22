<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Prototype;


use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Prototype([TestSuite::class])]
#[PrototypeRequiresAttribute(BeforeAll::class)]
interface TestSuiteBeforeAllPrototype {

    public function beforeAll() : Promise|Generator|Coroutine|null;

}