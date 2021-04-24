<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasSingleAfterAllHook;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

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