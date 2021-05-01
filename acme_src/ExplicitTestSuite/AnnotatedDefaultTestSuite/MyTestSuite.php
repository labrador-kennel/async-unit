<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AnnotatedDefaultTestSuite;

use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {}