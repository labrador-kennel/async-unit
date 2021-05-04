<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasResultPrinterPlugin;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkSomething() {
        $this->assert()->stringEquals('foo', 'foo');
    }

}