<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\BeforeAllTestSuiteHook;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    private int $counter = 0;

    #[BeforeAll]
    public function incrementCounter() : void {
        $this->counter++;
    }

    public function getCounter() : int {
        return $this->counter;
    }

}