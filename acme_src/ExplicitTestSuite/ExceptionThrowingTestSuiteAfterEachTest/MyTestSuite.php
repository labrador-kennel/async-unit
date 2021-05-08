<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteAfterEachTest;

use Cspray\Labrador\AsyncUnit\Attribute\AfterEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[AfterEachTest]
    public function throwEachTestException() {
        throw new \RuntimeException('AttachToTestSuite AfterEachTest');
    }

}