<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestDisabledEvents;

use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testFailingFloatEquals() {
        yield $this->asyncAssert()->not()->floatEquals(3.14, new Success(3.14));
    }

    #[Test]
    public function testIsTrue() {
        yield $this->asyncAssert()->isTrue(new Success(true));
    }

    #[Test]
    #[Disabled]
    public function testIsDisabled() {

    }

}