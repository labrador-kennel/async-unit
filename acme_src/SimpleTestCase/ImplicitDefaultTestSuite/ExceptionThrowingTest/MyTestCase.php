<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\ExceptionThrowingTest;

use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function throwsException() {
        throw new \Exception('Test failure');
    }

}