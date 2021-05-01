<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinesTestSuite;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\TestSuite as TestSuiteAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;

#[TestSuiteAttribute(MyFirstTestSuite::class)]
class FirstTestCase extends TestCase {

    #[Test]
    public function ensureIntEquals() {
        $this->assert()->intEquals(42, 42);
    }
}