<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\HasSingleAfterEachHook;

use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    private array $data = [];

    #[AfterEach]
    public function afterEach() {
        $this->data[] = 'afterEach';
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