<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncTesting\Attribute\Protocol;
use Cspray\Labrador\AsyncTesting\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Generator;

#[Protocol]
#[ProtocolRequiresAttribute(Test::class)]
interface TestProtocol {

    public function test() : Promise|Generator|Coroutine|null;

}