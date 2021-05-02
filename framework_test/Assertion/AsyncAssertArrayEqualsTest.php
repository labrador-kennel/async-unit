<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertArrayEqualsTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonArrayProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected, Promise|Generator|Coroutine $actual) : AsyncAssertion {
        return new AsyncAssertArrayEquals($expected, $actual);
    }

    protected function getExpectedValue() : array {
        return ['generators', 'promises', 'coroutines'];
    }

    protected function getBadValue() : array {
        return ['blocks', 'io', 'nooooo'];
    }

    protected function getExpectedType() : string {
        return 'array';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }
}