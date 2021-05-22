<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsExceptionDoesNotThrow;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkDoesNotThrow() {
        $this->expect()->exception(InvalidArgumentException::class);

        $this->assert()->isEmpty([]);
    }

}