<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BeforeEachAttributeOnNotTestCaseOrTestSuite;

use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeEach;
use Cspray\Labrador\AsyncTesting\Attribute\Test;

class BadTestCase {

    // We forgot to implement TestCase

    #[BeforeEach]
    public function ensureSomething() {

    }

}