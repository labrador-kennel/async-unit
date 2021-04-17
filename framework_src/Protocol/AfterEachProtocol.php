<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncTesting\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncTesting\Attribute\AfterEach;
use Cspray\Labrador\AsyncTesting\Attribute\Protocol;
use Cspray\Labrador\AsyncTesting\Attribute\ProtocolRequiresAttribute;
use Generator;

#[Protocol]
#[ProtocolRequiresAttribute(AfterEach::class)]
interface AfterEachProtocol {

    public function afterEach() : Promise|Generator|Coroutine|null;

}