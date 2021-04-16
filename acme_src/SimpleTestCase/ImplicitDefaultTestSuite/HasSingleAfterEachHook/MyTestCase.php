<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\HasSingleAfterEachHook;

use Cspray\Labrador\AsyncTesting\Attribute\AfterEach;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase implements TestCase {

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