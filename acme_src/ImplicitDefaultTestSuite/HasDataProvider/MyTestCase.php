<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasDataProvider;

use Cspray\Labrador\AsyncUnit\Attribute\DataProvider;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    private int $counter = 0;

    public function myDataProvider() : array {
        return [
            ['foo', 'foo'],
            ['bar', 'bar'],
            ['baz', 'baz']
        ];
    }

    #[Test]
    #[DataProvider('myDataProvider')]
    public function ensureStringsEqual(string $expected, string $actual) : void {
        $this->counter++;
        $this->assert()->stringEquals($expected, $actual);
    }

    public function getCounter() : int {
        return $this->counter;
    }
}