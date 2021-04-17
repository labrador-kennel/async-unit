<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncTesting\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeEach;
use Cspray\Labrador\AsyncTesting\Attribute\Protocol;
use Cspray\Labrador\AsyncTesting\Attribute\ProtocolRequiresAttribute;
use Generator;

#[Protocol]
#[ProtocolRequiresAttribute(BeforeEach::class)]
interface BeforeEachProtocol {

    public function beforeEach() : Promise|Generator|Coroutine|null;

}