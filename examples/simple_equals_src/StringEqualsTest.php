<?php declare(strict_types=1);

namespace Acme\Examples\SimpleEquals;

use Amp\Delayed;
use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use function Amp\call;

class StringEqualsTest extends TestCase {

    #[Test]
    public function ensureAssertionWorks() {
        $this->assert()->stringEquals('foo', 'foo');
    }

    #[Test]
    public function ensureAsyncAssertionWorks() {
        yield new Delayed(100);
        yield $this->asyncAssert()->stringEquals('async foo', $this->getPromisedValue());
    }

    #[Test]
    public function whatHappensIfAssertionFails() {
        yield new Delayed(200);
        $this->assert()->stringEquals('foo', 'bar');
    }

    #[Test]
    public function andAsyncAssertionFails() {
        yield new Delayed(100);
        yield $this->asyncAssert()->stringEquals('async foo', $this->getBadPromisedValue());
    }

    #[Test]
    public function whatHappensIfWeForgetToWriteAssertion() {

    }

    private function getPromisedValue() : Promise {
        return call(function() {
            yield new Delayed(200);
            return 'async foo';
        });
    }

    private function getBadPromisedValue() : Promise {
        return new Success('async bar');
    }

}