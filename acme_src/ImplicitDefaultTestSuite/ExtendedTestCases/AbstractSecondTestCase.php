<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;


use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;

abstract class AbstractSecondTestCase extends FirstTestCase {

    #[Test]
    public function secondEnsureSomething() {
        $this->assert()->intEquals(42, 42);
        yield $this->asyncAssert()->isFalse(new Success(false));
    }

}