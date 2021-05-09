<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BadNamespaceTestSuiteBeforeEachTest\IntentionallyBad;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeEachTest;
use Cspray\Labrador\AsyncUnit\TestSuite;

class MyTestSuite extends TestSuite {

    #[BeforeEachTest]
    public function checkEach() {

    }

}