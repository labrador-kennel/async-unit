<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Generator;

#[Protocol([TestSuite::class, TestCase::class])]
#[ProtocolRequiresAttribute(BeforeEach::class)]
interface BeforeEachProtocol {

    public function beforeEach() : Promise|Generator|Coroutine|null;

}