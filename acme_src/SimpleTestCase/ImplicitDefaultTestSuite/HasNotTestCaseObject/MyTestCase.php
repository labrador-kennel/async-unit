<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\HasNotTestCaseObject;

use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    public function getName() {
        return self::class;
    }

    #[Test]
    public function ensureSomethingHappens() {

    }
}