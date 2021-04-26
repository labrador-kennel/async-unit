<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\FailedNotAssertion;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkFailedNotAssertion() {
        $this->assert()->not()->stringEquals('foo', 'foo');
    }

}