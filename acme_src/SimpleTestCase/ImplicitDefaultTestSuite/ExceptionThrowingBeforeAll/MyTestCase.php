<?php declare(strict_types=1);


namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\ExceptionThrowingBeforeAll;


use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    #[BeforeAll]
    public static function beforeAll() {
        throw new \RuntimeException('Thrown in the class beforeAll');
    }

    #[Test]
    public static function ensureSomething() {

    }


}