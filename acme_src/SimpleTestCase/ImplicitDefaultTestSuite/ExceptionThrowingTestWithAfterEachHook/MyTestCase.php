<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\ExceptionThrowingTestWithAfterEachHook;

use Cspray\Labrador\AsyncTesting\Attribute\AfterEach;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    private bool $afterHookCalled = false;

    #[Test]
    public function throwsException() {
        throw new \Exception('Test failure');
    }

    #[AfterEach]
    public function afterExceptionThrown() {
        $this->afterHookCalled = true;
    }

    public function getAfterHookCalled() {
        return $this->afterHookCalled;
    }

}