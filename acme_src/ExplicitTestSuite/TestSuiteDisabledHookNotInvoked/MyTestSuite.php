<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabledHookNotInvoked;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[Disabled]
class MyTestSuite extends TestSuite {

    private array $state = [];

    #[BeforeAll]
    public function beforeAll() {
        $this->state[] = 'beforeAll';
    }

    #[BeforeEach]
    public function beforeEachTestCase() {
        $this->state[] = 'beforeEach';
    }

    #[BeforeEachTest]
    public function beforeEachTest() {
        $this->state[] = 'beforeEachTest';
    }

    #[AfterEachTest]
    public function afterEachTest() {
        $this->state[] = 'afterEachTest';
    }

    #[AfterEach]
    public function afterEachTestCase() {
        $this->state[] = 'afterEach';
    }

    #[AfterAll]
    public function afterAll() {
        $this->state[] = 'afterAll';
    }

    public function getState() : array {
        return $this->state;
    }

}