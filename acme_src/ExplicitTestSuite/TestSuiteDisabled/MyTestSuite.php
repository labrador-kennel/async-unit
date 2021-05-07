<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabled;

use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[Disabled]
class MyTestSuite extends TestSuite {

}