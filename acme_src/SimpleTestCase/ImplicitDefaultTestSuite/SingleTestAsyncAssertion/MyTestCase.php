<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\SingleTestAsyncAssertion;

use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function ensureAsyncAssert() {
        yield $this->asyncAssert()->stringEquals('foo', new Success('foo'));
    }

}