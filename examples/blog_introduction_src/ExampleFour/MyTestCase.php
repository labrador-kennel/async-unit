<?php declare(strict_types=1);

namespace Acme\Examples\BlogIntroduction\ExampleFour;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Amp\Delayed;
use Amp\Promise;
use Generator;
use function Amp\call;

function getAsyncString() : Promise {
    return call(function() {
        yield new Delayed(100); // emulating some interaction with I/O
        return 'AsyncUnit';
    });
}

class MyTestCase extends TestCase {

    #[Test]
    public function checkStringEquals() : void {
        $this->assert()->stringEquals('AsyncUnit', 'AsyncUnit');
    }

    #[Test]
    public function checkAsyncIo() : Generator {
        yield $this->asyncAssert()->stringEquals('AsyncUnit', getAsyncString());
    }
}