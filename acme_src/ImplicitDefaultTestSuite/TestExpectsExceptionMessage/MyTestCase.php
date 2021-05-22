<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestExpectsExceptionMessage;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkExceptionMessage() {
        $this->expect()->exception(InvalidArgumentException::class);
        $this->expect()->exceptionMessage('This is the message that I expect');

        throw new InvalidArgumentException('This is the message that I expect');
    }

}