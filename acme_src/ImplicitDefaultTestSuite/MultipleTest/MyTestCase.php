<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTest;

use Amp\Delayed;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use function Amp\call;

class MyTestCase extends TestCase {

    private array $invoked = [];

    #[Test]
    public function ensureSomethingHappens() {
        yield new Delayed(100);
        $this->invoked[] = __METHOD__;
        $this->assert()->stringEquals('foo', 'foo');
    }

    #[Test]
    public function ensureSomethingHappensTwice() {
        $this->invoked[] = __METHOD__;
        $this->assert()->not()->stringEquals('AsyncUnit', 'PHPUnit');
    }

    #[Test]
    public function ensureSomethingHappensThreeTimes() {
        return call(function() {
            $this->invoked[] = self::class . '::ensureSomethingHappensThreeTimes';
            $this->assert()->intEquals(42, 42);
        });
    }

    public function getName() : string {
        return self::class;
    }

    public function getInvokedMethods() : array {
        return $this->invoked;
    }
}