<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Generator;

#[Protocol]
#[ProtocolRequiresAttribute(AfterEach::class)]
interface AfterEachProtocol {

    public function afterEach() : Promise|Generator|Coroutine|null;

}