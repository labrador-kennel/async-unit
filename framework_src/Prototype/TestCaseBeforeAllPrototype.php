<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Prototype;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(BeforeAll::class)]
interface TestCaseBeforeAllPrototype {

    public static function beforeAll(TestSuite $testSuite) : Promise|\Generator|Coroutine|null;

}