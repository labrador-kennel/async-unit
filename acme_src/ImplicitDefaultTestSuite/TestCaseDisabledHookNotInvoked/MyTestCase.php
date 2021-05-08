<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseDisabledHookNotInvoked;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

#[Disabled]
class MyTestCase extends TestCase {

    private static array $state = [];

    #[BeforeAll]
    public static function beforeAll() {
        self::$state[] = 'beforeAll';
    }

    #[BeforeEach]
    public function before() {
        self::$state[] = 'before';
    }

    #[Test]
    public function testOne() {
        self::$state[] = 'testOne';
    }

    #[Test]
    public function testTwo() {
        self::$state[] = 'testTwo';
    }

    #[AfterEach]
    public function afterEach() {
        self::$state[] = 'afterEach';
    }

    #[AfterAll]
    public static function afterAll() {
        self::$state[] = 'afterAll';
    }

    public function getState() : array {
        return self::$state;
    }

}