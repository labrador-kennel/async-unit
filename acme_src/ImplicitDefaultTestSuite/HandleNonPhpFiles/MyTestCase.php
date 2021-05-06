<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HandleNonPhpFiles;

use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    #[Test]
    public function checkAsyncNull() {
        yield $this->asyncAssert()->isNull(new Success(null));
    }

}