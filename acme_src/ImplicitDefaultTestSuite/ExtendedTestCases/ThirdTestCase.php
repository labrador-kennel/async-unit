<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExtendedTestCases;

use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;

class ThirdTestCase extends AbstractSecondTestCase {

    #[Test]
    public function thirdEnsureSomething() {
        yield $this->asyncAssert()->arrayEquals([1,2,3], new Success([1,2,3]));
        $this->assert()->stringEquals('bar', 'bar');
        $this->assert()->isNull(null);
    }

}