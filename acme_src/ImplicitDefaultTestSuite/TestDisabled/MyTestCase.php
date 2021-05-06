<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestDisabled;

use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkSomething() {
        $this->assert()->stringEquals('AsyncUnit', 'AsyncUnit');
    }

    #[Test]
    #[Disabled]
    public function skippedTest() {
        throw new \RuntimeException('We should not actually execute this function');
    }

}