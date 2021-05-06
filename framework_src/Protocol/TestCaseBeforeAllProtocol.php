<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Protocol;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Protocol;
use Cspray\Labrador\AsyncUnit\Attribute\ProtocolRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[Protocol([TestCase::class])]
#[ProtocolRequiresAttribute(BeforeAll::class)]
interface TestCaseBeforeAllProtocol {

    public static function beforeAll(TestSuite $testSuite) : Promise|\Generator|Coroutine|null;

}