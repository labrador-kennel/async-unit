<?php declare(strict_types=1);


namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\MultipleTestCase;


use Amp\Delayed;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class BarTestCase extends TestCase {

    private bool $testInvoked = false;

    #[Test]
    public function ensureSomething() {
        yield new Delayed(100);
        $this->testInvoked = true;
    }

    public function getName() : string {
        return self::class;
    }

    public function getTestInvoked() : bool {
        return $this->testInvoked;
    }
}