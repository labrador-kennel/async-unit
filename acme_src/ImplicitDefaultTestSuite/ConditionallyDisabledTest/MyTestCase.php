<?php

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ConditionallyDisabledTest;

use Cspray\Labrador\AsyncUnit\Attribute\DisabledIf;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    public function isDisabled() : bool {
        return true;
    }

    public function isNotDisabled() : bool {
        return false;
    }

    #[Test]
    #[DisabledIf('isDisabled', 'Conditionally disabled reason')]
    public function testIsDisabled() : void {
        throw new \RuntimeException('Should never happen');
    }

    #[Test]
    #[DisabledIf('isNotDisabled')]
    public function testIsNotDisabled() : void {
        $this->assert()->isTrue(true);
    }


}