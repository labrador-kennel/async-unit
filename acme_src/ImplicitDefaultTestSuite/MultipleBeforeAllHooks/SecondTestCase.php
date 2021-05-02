<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleBeforeAllHooks;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class SecondTestCase extends TestCase {

    private static string $state = '';

    #[BeforeAll]
    public static function setStateAgain() : void {
        self::$state = self::class;
    }

    #[Test]
    public function checkState() {
        $this->assert()->stringEquals(self::class, self::$state);
    }

    public function getState() : string {
        return self::$state;
    }

}