<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Protocol;


use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Protocol([TestSuite::class])]
#[ProtocolRequiresAttribute(BeforeAll::class)]
interface TestSuiteBeforeAllProtocol {

    public function beforeAll() : Promise|Generator|Coroutine|null;

}