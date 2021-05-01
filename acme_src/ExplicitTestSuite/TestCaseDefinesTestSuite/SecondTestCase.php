<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinesTestSuite;

use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\Attribute\TestSuite as TestSuiteAttribute;
use Generator;

#[TestSuiteAttribute(MySecondTestSuite::class)]
class SecondTestCase extends TestCase {

    #[Test]
    public function ensureSomethingIsNull() : Generator {
        yield $this->asyncAssert()->isNull(new Success(null));
    }

}