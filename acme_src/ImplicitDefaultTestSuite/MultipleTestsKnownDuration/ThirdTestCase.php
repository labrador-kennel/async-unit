<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTestsKnownDuration;

use Amp\Delayed;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class ThirdTestCase extends TestCase {

    #[Test]
    public function checkOne() {
        yield new Delayed(100);
        $this->assert()->floatEquals(3.14, 3.14);
    }

    #[Test]
    public function checkTwo() {
        yield new Delayed(100);
        yield $this->asyncAssert()->stringEquals('AsyncUnit', new Success('AsyncUnit'));
    }

    #[Test]
    public function checkThree() {
        yield new Delayed(100);
        $this->assert()->countEquals(2, ['a', 0]);
    }

}