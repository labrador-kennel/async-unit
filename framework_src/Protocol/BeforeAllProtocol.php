<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;

#[Protocol]
#[ProtocolRequiresAttribute(BeforeAll::class)]
interface BeforeAllProtocol {

    public static function beforeAll() : Promise|\Generator|Coroutine|null;

}