<?php

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteHookPriority;

use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

#[AttachToTestSuite(MyTestSuite::class)]
class MyTestCase extends TestCase {

    #[Test]
    public function testSomething() {
        $this->assert()->isTrue(true);
    }

}