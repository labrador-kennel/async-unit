<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteAfterEachTest\IntentionallyBad;

use Cspray\Labrador\AsyncUnit\Attribute\AfterEachTest;
use Cspray\Labrador\AsyncUnit\TestSuite;

class MyTestSuite extends TestSuite {

    #[AfterEachTest]
    public function checkEach() {

    }

}