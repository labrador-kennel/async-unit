<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabledHookNotInvoked;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite;
use Cspray\Labrador\AsyncUnit\TestCase;

#[AttachToTestSuite(MyTestSuite::class)]
class MyTestCase extends TestCase {

    #[Test]
    public function testSomething() {

    }

}