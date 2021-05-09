<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteAfterEach\IntentionallyBad;

use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\TestSuite;

class MyTestSuite extends TestSuite {

    #[AfterEach]
    public function checkEach() {

    }

}