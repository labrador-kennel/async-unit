<?php declare(strict_types=1);


namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinedAndImplicitDefaultTestSuite;


use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\TestSuite;
use Cspray\Labrador\AsyncUnit\TestCase;

#[TestSuite(MyTestSuite::class)]
class SecondTestCase extends TestCase {

    #[Test]
    public function ensureSomething() {
        $this->assert()->isFalse(false);
    }

}