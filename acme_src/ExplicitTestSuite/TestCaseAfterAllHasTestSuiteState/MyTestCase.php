<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseAfterAllHasTestSuiteState;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;

class MyTestCase extends TestCase {

    private static ?string $state = null;

    #[AfterAll]
    public static function setState(TestSuite $testSuite) {
        self::$state = $testSuite->get('state');
    }

    #[Test]
    public function checkState() {
        $this->assert()->isNull(self::$state);
    }

    public function getState() : ?string {
        return self::$state;
    }

}