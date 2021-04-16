<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\MultipleTestCase;

use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class BazTestCase implements TestCase {

    private bool $testInvoked = false;

    #[Test]
    public function ensureSomething() {
        $this->testInvoked = true;
    }

    public function getName() : string {
        return self::class;
    }

    public function getTestInvoked() : bool {
        return $this->testInvoked;
    }
}