<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BeforeEachAttributeOnNotTestCaseOrTestSuite;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Test;

class BadTestCase {

    // We forgot to implement TestCase

    #[BeforeEach]
    public function ensureSomething() {

    }

}