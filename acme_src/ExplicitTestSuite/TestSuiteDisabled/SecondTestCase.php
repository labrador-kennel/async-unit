<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabled;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite;
use Cspray\Labrador\AsyncUnit\TestCase;

#[AttachToTestSuite(MyTestSuite::class)]
class SecondTestCase extends TestCase {

    #[Test]
    public function testOne() {
        throw new \RuntimeException();
    }

}