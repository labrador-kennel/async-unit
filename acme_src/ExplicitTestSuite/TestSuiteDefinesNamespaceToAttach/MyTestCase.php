<?php

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDefinesNamespaceToAttach;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function testSomething() {
        $this->assert()->stringEquals('AsyncUnit', 'AsyncUnit');
    }

}