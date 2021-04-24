<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;

use Cspray\Labrador\AsyncUnit\Attribute\Test;

abstract class AbstractFourthTestCase extends ThirdTestCase {

    #[Test]
    public function fourthEnsureSomething() {

    }

}