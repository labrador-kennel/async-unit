<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\HasSingleBeforeEachHook;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    private array $data = [];

    #[BeforeEach]
    public function beforeEach() {
        $this->data[]  = 'beforeEach';
    }

    #[Test]
    public function ensureSomething() {
        $this->data[] = 'ensureSomething';
    }

    #[Test]
    public function ensureSomethingTwice() {
        $this->data[] = 'ensureSomethingTwice';
    }

    public function getData() : array {
        return $this->data;
    }
}