<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseHasTimeout;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\Timeout;
use Cspray\Labrador\AsyncUnit\TestCase;

#[Timeout(150)]
class MyTestCase extends TestCase {

    #[Test]
    public function testOne() {

    }

    #[Test]
    public function testTwo() {

    }
}