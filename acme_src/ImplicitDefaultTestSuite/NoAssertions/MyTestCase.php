<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\NoAssertions;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function noAssertions() {

    }

}