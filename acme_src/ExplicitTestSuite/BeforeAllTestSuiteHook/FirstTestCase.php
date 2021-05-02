<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\BeforeAllTestSuiteHook;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function ensureSuiteCounter() : void {
        $this->assert()->intEquals(1, $this->testSuite()->getCounter());
    }

    #[Test]
    public function ensureSuiteCounterAgain() : void {
        $this->assert()->intEquals(1, $this->testSuite()->getCounter());
    }

}