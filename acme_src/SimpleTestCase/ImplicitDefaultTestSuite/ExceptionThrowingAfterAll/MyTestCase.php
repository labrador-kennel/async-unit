<?php declare(strict_types=1);


namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\ExceptionThrowingAfterAll;


use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    #[AfterAll]
    public static function afterAll() {
        throw new \RuntimeException('Thrown in the class afterAll');
    }

    #[Test]
    public static function ensureSomething() {

    }

}