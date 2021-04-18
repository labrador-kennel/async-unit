<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Amp\Success;
use Cspray\Labrador\AsyncUnit\TestCase;

class FailingTestCase extends TestCase {

    public function doFailure() {
        $this->assert()->stringEquals('foo', 'bar');
    }

    public function doAsyncFailure() {
        yield $this->asyncAssert()->stringEquals('foo', new Success('bar'));
    }

}