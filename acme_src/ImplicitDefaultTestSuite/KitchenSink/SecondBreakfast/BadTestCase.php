<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class BadTestCase extends TestCase {

    #[Test]
    public function throwException() {
        throw new \RuntimeException(__METHOD__);
    }

}