<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\WithAsyncUnitConfig\tests\Bar;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class FooTestCase extends TestCase {

    #[Test]
    public function testIntEquals() {
        $this->assert()->intEquals(1, 1);
    }

}