<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Generator;

#[Protocol([TestCase::class])]
#[ProtocolRequiresAttribute(Test::class)]
interface TestProtocol {

    public function test() : Promise|Generator|Coroutine|null;

}