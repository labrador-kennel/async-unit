<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterEachTestSuiteHook;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class ThirdTestCase extends TestCase {

    #[Test]
    public function testFoo() : void {
        $this->assert()->arrayEquals([], $this->testSuite()->getState());
    }

    #[Test]
    public function testBar() : void {
        $this->assert()->arrayEquals([], $this->testSuite()->getState());
    }

}