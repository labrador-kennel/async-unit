<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\DataProvider;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Generator;

#[Protocol([TestCase::class])]
#[ProtocolRequiresAttribute(DataProvider::class)]
#[ProtocolRequiresAttribute(Test::class)]
interface DataProviderTestProtocol {

    public function test(mixed ...$args) : Promise|Generator|Coroutine|null;

}