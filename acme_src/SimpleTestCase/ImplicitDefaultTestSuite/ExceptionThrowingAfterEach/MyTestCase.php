<?php declare(strict_types=1);


namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\ExceptionThrowingAfterEach;


use Cspray\Labrador\AsyncTesting\Attribute\AfterEach;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    #[AfterEach]
    public function afterEach() {
        throw new \RuntimeException('Thrown in the object afterEach');
    }

    #[Test]
    public static function ensureSomething() {

    }


}