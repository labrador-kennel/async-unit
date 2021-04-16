<?php declare(strict_types=1);

namespace Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite\HasSingleBeforeAllHook;

use Amp\Delayed;
use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;
use Generator;

class MyTestCase implements TestCase {

    private static array $staticData = [];
    private array $objectData = [];

    public function getName() : string {
        return self::class;
    }

    #[BeforeAll]
    public static function beforeAll() : Generator {
        yield new Delayed(100);
        self::$staticData[] = 'beforeAll';
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
        return array_merge([], self::$staticData, $this->objectData);
    }
}