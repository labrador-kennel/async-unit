<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingAfterAll;


use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[AfterAll]
    public static function afterAll() {
        throw new \RuntimeException('Thrown in the class afterAll');
    }

    #[Test]
    public static function ensureSomething() {

    }

}