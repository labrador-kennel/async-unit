<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEach;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[BeforeEach]
    public function throwEachException() {
        throw new \RuntimeException('TestSuite BeforeEach');
    }

}