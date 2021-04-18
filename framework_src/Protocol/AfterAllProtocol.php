<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Generator;

#[Protocol]
#[ProtocolRequiresAttribute(AfterAll::class)]
interface AfterAllProtocol {

    public function afterAll() : Promise|Generator|Coroutine|null;

}