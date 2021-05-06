<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Protocol([TestCase::class])]
#[ProtocolRequiresAttribute(AfterAll::class)]
interface TestCaseAfterAllProtocol {

    public static function afterAll(TestSuite $testSuite) : Promise|Generator|Coroutine|null;

}