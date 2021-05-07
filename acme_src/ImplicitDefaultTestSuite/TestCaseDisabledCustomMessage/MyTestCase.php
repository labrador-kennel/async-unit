<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseDisabledCustomMessage;

use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

#[Disabled('The TestCase is disabled')]
class MyTestCase extends TestCase {

    #[Test]
    public function testOne() {

    }

}