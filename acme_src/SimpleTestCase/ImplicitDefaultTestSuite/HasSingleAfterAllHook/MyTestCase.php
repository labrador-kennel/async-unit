<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\HasSingleAfterAllHook;

use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class MyTestCase extends TestCase {

    private static array $classData = [];
    private array $objectData = [];

    #[AfterAll]
    public static function afterAll() {
        self::$classData[] = 'afterAll';
    }

    #[Test]
    public function ensureSomething() {
        $this->objectData[] = 'ensureSomething';
    }

    #[Test]
    public function ensureSomethingTwice() {
        $this->objectData[] = 'ensureSomethingTwice';
    }

    public function getCombinedData() : array {
        return array_merge([], self::$classData, $this->objectData);
    }
}