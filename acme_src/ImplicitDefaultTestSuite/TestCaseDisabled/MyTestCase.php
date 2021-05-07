<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseDisabled;

use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

#[Disabled]
class MyTestCase extends TestCase {

    private array $data = [];

    #[Test]
    public function skippedOne() {
        $this->data[] = '1';
    }

    #[Test]
    public function skippedTwo() {
        $this->data[] = '2';
    }

    #[Test]
    public function skippedThree() {
        $this->data[] = '3';
    }

    public function getData() : array {
        return $this->data;
    }

}