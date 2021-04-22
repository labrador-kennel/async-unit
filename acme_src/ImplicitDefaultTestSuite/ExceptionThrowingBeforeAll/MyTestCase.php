<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingBeforeAll;


use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[BeforeAll]
    public static function beforeAll() {
        throw new \RuntimeException('Thrown in the class beforeAll');
    }

    #[Test]
    public static function ensureSomething() {

    }


}