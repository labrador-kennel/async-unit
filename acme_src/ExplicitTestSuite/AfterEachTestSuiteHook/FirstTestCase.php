<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterEachTestSuiteHook;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function testSomething() : void {
        $this->assert()->arrayEquals([], $this->testSuite()->getState());
    }

    #[Test]
    public function testSomethingElse() : void {
        $this->assert()->arrayEquals([], $this->testSuite()->getState());
    }

    #[Test]
    public function testItAgain() : void {
        $this->assert()->arrayEquals([], $this->testSuite()->getState());
    }

}