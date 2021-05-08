<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteAfterAll;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\TestSuite;
use RuntimeException;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[AfterAll]
    public function throwException() {
        throw new RuntimeException('AttachToTestSuite AfterAll');
    }

}