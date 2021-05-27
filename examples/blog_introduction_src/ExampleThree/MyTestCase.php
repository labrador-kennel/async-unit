<?php declare(strict_types=1);

namespace Acme\Examples\BlogIntroduction\ExampleThree;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkStringEquals() : void {
        $this->assert()->stringEquals('AsyncUnit', 'AsyncUnit');
    }

}