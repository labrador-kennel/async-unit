<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsNoAsyncAssertionsAssertMade;

use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function noAssertionButAsyncAssertionMade() {
        $this->expect()->noAssertions();

        yield $this->asyncAssert()->isNull(new Success(null));
        yield $this->asyncAssert()->isEmpty(new Success([]));
    }

}