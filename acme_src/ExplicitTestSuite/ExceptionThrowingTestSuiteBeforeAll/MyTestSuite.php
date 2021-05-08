<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeAll;

use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\TestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[BeforeAll]
    public function throwException() {
        throw new \RuntimeException('Thrown in AttachToTestSuite');
    }

}