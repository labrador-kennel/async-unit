<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTestsKnownDuration;

use Amp\Delayed;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function checkOne() {
        yield new Delayed(100);
        $this->assert()->arrayEquals([1, 2, 3], [1, 2, 3]);
    }

}