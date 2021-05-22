<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsNoAssertionsAssertMade;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testNoAssertionAssertionMade() : void {
        $this->expect()->noAssertions();
        $this->assert()->isNull(null);
        $this->assert()->isTrue(true);
    }

}