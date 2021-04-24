<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;


use Cspray\Labrador\AsyncUnit\Attribute\Test;

abstract class AbstractSecondTestCase extends FirstTestCase {

    #[Test]
    public function secondEnsureSomething() {

    }

}