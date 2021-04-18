<?php declare(strict_types=1);


namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\SingleTest;

use Amp\Delayed;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Generator;

class MyTestCase extends TestCase {

    private bool $testInvoked = false;

    #[Test]
    public function ensureSomethingHappens() : Generator {
        yield new Delayed(10);
        $this->testInvoked = true;
        $this->assert()->stringEquals('foo', 'foo');
    }

    public function getTestInvoked() : bool {
        return $this->testInvoked;
    }
}