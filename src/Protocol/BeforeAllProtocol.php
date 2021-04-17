<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncTesting\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\Protocol;
use Cspray\Labrador\AsyncTesting\Attribute\ProtocolRequiresAttribute;

#[Protocol]
#[ProtocolRequiresAttribute(BeforeAll::class)]
interface BeforeAllProtocol {

    public static function beforeAll() : Promise|\Generator|Coroutine|null;

}