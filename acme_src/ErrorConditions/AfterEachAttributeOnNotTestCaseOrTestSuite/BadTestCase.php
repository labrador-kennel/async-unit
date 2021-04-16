<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\AfterEachAttributeOnNotTestCaseOrTestSuite;

use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;
use Cspray\Labrador\AsyncTesting\Attribute\AfterEach;

class BadTestCase {

    // We forgot to implement TestCase

    #[AfterEach]
    public function ensureSomething() {

    }

}