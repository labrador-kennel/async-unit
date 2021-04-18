<?php declare(strict_types=1);


namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\ExceptionThrowingBeforeEach;


use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[BeforeEach]
    public function beforeEach() {
        throw new \RuntimeException('Thrown in the object beforeEach');
    }

    #[Test]
    public static function ensureSomething() {

    }


}