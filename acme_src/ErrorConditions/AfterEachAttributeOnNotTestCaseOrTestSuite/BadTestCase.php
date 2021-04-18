<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\AfterEachAttributeOnNotTestCaseOrTestSuite;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;

class BadTestCase {

    // We forgot to implement TestCase

    #[AfterEach]
    public function ensureSomething() {

    }

}