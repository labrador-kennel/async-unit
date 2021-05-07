<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestDisabledCustomMessage;

use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Disabled('Not sure what we should do here yet')]
    #[Test]
    public function testOne() {

    }

}