<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\WithAsyncUnitConfig\tests\Foo;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class BarTestCase extends TestCase {

    #[Test]
    public function ensureStringEquals() {
        $this->assert()->not()->stringEquals('foo', 'bar');
    }

}