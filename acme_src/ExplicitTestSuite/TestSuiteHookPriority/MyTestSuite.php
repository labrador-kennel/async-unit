<?php

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteHookPriority;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEachTest;
use Cspray\Labrador\AsyncUnit\TestSuite;

class MyTestSuite extends TestSuite {

    private array $invokedHooks = [];

    #[BeforeAll(1)]
    public function beforeAllOne() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[BeforeAll(3)]
    public function beforeAllThree() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[BeforeAll(2)]
    public function beforeAllTwo() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[BeforeEach(3)]
    public function beforeEachThree() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[BeforeEach(1)]
    public function beforeEachOne() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[BeforeEach(2)]
    public function beforeEachTwo() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[BeforeEachTest(1)]
    public function beforeEachTestOne() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[BeforeEachTest(3)]
    public function beforeEachTestThree() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[BeforeEachTest(2)]
    public function beforeEachTestTwo() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterAll(1)]
    public function afterAllOne() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterAll(3)]
    public function afterAllThree() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterAll(2)]
    public function afterAllTwo() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterEach(3)]
    public function afterEachThree() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterEach(1)]
    public function afterEachOne() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterEach(2)]
    public function afterEachTwo() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterEachTest(1)]
    public function afterEachTestOne() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterEachTest(3)]
    public function afterEachTestThree() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    #[AfterEachTest(2)]
    public function afterEachTestTwo() {
        $this->invokedHooks[] = __FUNCTION__;
    }

    public function getInvokedHooks() : array {
        return $this->invokedHooks;
    }
}