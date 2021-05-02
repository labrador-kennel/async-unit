<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterAllTestSuiteHook;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class SecondTestCase extends TestCase {

    #[Test]
    public function ensureSuiteCounter() : void {
        $this->assert()->intEquals(0, $this->testSuite()->getCounter());
    }

}