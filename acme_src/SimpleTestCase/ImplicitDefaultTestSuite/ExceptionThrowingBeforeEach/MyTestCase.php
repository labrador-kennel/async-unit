<?php declare(strict_types=1);


namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\ExceptionThrowingBeforeEach;


use Cspray\Labrador\AsyncTesting\Attribute\BeforeEach;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    #[BeforeEach]
    public function beforeEach() {
        throw new \RuntimeException('Thrown in the object beforeEach');
    }

    #[Test]
    public static function ensureSomething() {

    }


}