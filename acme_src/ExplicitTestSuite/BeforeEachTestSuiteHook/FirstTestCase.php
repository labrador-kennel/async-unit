<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\BeforeEachTestSuiteHook;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function testSomething() : void {
        $this->assert()->stringEquals('foo', 'foo');
    }

    #[Test]
    public function testSomethingElse() : void {
        $this->assert()->stringEquals('bar', 'bar');
    }

    /**
     *
     */
    #[Test]
    public function testItAgain() : void {
        $this->assert()->stringEquals('baz', 'baz');
    }

}