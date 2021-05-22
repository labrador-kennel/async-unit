<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Prototype;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Prototype([TestSuite::class, TestCase::class])]
#[PrototypeRequiresAttribute(BeforeEach::class)]
interface BeforeEachPrototype {

    public function beforeEach() : Promise|Generator|Coroutine|null;

}