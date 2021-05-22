<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Prototype;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(AfterAll::class)]
interface TestCaseAfterAllPrototype {

    public static function afterAll(TestSuite $testSuite) : Promise|Generator|Coroutine|null;

}