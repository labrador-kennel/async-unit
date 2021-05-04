<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteStateBeforeAll;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function checkTestSuiteData() {
        $this->assert()->stringEquals('bar', $this->testSuite()->get('foo'));
    }

}