<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\AfterAllAttributeOnNotTestCaseOrTestSuite;

use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;

class BadTestCase {

    // We forgot to implement TestCase

    #[AfterAll]
    public static function ensureSomething() {

    }

}