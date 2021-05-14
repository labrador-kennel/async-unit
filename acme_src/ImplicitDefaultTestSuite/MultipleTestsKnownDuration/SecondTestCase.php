<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTestsKnownDuration;


use Amp\Delayed;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class SecondTestCase extends TestCase {

    #[Test]
    public function checkOne() {
        yield new Delayed(100);
        yield $this->asyncAssert()->isEmpty(new Success([]));
    }

    #[Test]
    public function checkTwo() {
        yield new Delayed(100);
        $this->assert()->isTrue(true);
    }

}