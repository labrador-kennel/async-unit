<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabledCustomMessage;

use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[Disabled('The TestSuite is disabled')]
class MyTestSuite extends TestSuite {

}