<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinesTestSuite;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\TestSuite as TestSuiteAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;

#[TestSuiteAttribute(MySecondTestSuite::class)]
class ThirdTestCase extends TestCase {

    #[Test]
    public function ensureStringEquals() {
        $this->assert()->stringEquals('AsyncUnit', 'AsyncUnit');
    }

}