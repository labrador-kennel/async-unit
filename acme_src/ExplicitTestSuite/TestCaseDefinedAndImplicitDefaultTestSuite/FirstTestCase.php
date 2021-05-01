<?php declare(strict_types=1);


namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinedAndImplicitDefaultTestSuite;


use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function ensureSomething() {
        $this->assert()->stringEquals('AsyncUnit', 'AsyncUnit');
    }

}