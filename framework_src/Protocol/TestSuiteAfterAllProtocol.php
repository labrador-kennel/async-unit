<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Protocol;


use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Protocol([TestSuite::class])]
#[ProtocolRequiresAttribute(AfterAll::class)]
interface TestSuiteAfterAllProtocol {

    public function afterAll() : Promise|Generator|Coroutine|null;

}