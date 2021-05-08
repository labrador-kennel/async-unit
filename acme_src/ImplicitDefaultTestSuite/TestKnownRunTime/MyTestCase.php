<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestKnownRunTime;

use Amp\Delayed;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testTiming() {
        yield new Delayed(500);
        yield $this->asyncAssert()->floatEquals(3.14, new Success(3.14));
    }

}