<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteBeforeEach\IntentionallyBad;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\TestSuite;

class MyTestSuite extends TestSuite {

    #[BeforeEach]
    public function checkEach() {

    }

}