<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\BeforeEachTestSuiteHook;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    private array $state = [];

    #[BeforeEach]
    public function addToState() : void {
        $this->state[] = 'AsyncUnit';
    }

    public function getState() : array {
        return $this->state;
    }

}