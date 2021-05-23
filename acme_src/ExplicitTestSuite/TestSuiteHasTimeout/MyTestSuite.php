<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteHasTimeout;

use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\Timeout;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[Timeout(125)]
#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

}