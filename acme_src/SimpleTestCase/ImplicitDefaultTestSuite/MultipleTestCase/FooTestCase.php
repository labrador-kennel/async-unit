<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\MultipleTestCase;

use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;
use function Amp\call;

class FooTestCase implements TestCase {

    private int $testCounter = 0;

    public function getName() : string {
        return self::class;
    }

    #[Test]
    public function ensureSomething() {
        return call(function() {
            $this->testCounter++;
        });
    }

    #[Test]
    public function ensureSomethingTwice() {
        return call(function() {
            $this->testCounter++;
        });
    }

    public function getTestCounter() {
        return $this->testCounter;
    }

}