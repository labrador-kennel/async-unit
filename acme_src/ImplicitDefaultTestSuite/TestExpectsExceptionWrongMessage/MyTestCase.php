<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsExceptionWrongMessage;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkExceptionMessage() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This is the message that I expect');

        throw new InvalidArgumentException('This is NOT the message that I expect');
    }

}