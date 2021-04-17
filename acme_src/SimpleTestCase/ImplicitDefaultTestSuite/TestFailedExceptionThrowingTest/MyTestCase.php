<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\TestFailedExceptionThrowingTest;

use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\Exception\TestFailedException;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function ensureSomethingFails() {
        throw new TestFailedException('Something barfed');
    }

}