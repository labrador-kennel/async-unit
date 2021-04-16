<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BeforeAllAttributeOnNotTestCaseOrTestSuite;

use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\Test;

class BadTestCase {

    // We forgot to implement TestCase

    #[BeforeAll]
    public static function ensureSomething() {

    }

}