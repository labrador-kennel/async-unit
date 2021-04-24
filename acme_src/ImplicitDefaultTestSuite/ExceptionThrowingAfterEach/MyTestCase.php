<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingAfterEach;


use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[AfterEach]
    public function afterEach() {
        throw new \RuntimeException('Thrown in the object afterEach');
    }

    #[Test]
    public static function ensureSomething() {

    }


}