<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncTesting\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;
use Cspray\Labrador\AsyncTesting\Attribute\Protocol;
use Cspray\Labrador\AsyncTesting\Attribute\ProtocolRequiresAttribute;
use Generator;

#[Protocol]
#[ProtocolRequiresAttribute(AfterAll::class)]
interface AfterAllProtocol {

    public function afterAll() : Promise|Generator|Coroutine|null;

}