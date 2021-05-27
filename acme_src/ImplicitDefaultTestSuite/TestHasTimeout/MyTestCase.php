<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestHasTimeout;

use Amp\Delayed;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\Timeout;
use Cspray\Labrador\AsyncUnit\TestCase;
use Generator;

class MyTestCase extends TestCase {

    #[Test]
    #[Timeout(100)]
    public function timeOutTest() : Generator {
        yield new Delayed(200);
        $this->assert()->stringEquals('a', 'a');
    }

}