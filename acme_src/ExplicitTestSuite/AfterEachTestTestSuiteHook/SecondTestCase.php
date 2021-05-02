<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterEachTestTestSuiteHook;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class SecondTestCase extends TestCase {

    #[Test]
    public function ensureIntEquals() : void {
        $this->assert()->arrayEquals([], $this->testSuite()->getState());
    }

}