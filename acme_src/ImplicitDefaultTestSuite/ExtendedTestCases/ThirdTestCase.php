<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;

use Cspray\Labrador\AsyncUnit\Attribute\Test;

class ThirdTestCase extends AbstractSecondTestCase {

    #[Test]
    public function thirdEnsureSomething() {

    }

}