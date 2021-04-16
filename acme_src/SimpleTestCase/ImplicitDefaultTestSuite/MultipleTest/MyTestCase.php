<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\MultipleTest;

use Amp\Delayed;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;
use function Amp\call;

class MyTestCase implements TestCase {

    private array $invoked = [];

    #[Test]
    public function ensureSomethingHappens() {
        yield new Delayed(100);
        $this->invoked[] = __METHOD__;
    }

    #[Test]
    public function ensureSomethingHappensTwice() {
        $this->invoked[] = __METHOD__;
    }

    #[Test]
    public function ensureSomethingHappensThreeTimes() {
        return call(function() {
            $this->invoked[] = __METHOD__;
        });
    }

    public function getName() : string {
        return self::class;
    }

    public function getInvokedMethods() : array {
        return $this->invoked;
    }
}