<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseBeforeAllHasTestSuiteState;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;

class MyTestCase extends TestCase {

    private static string $state;

    #[BeforeAll]
    public static function setState(TestSuite $testSuite) {
        self::$state = $testSuite->get('state');
    }

    #[Test]
    public function checkState() {
        $this->assert()->stringEquals('AsyncUnit', self::$state);
    }

}