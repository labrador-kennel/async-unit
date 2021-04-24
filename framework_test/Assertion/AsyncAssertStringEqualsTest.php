<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertStringEquals
 */
class AsyncAssertStringEqualsTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonStringProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected, Promise|Generator|Coroutine $actual) : AsyncAssertion {
        return new AsyncAssertStringEquals($expected, $actual);
    }

    protected function getExpectedValue() : string {
        return 'async unit';
    }

    protected function getBadValue() : string {
        return 'blocking code';
    }

    protected function getExpectedType() : string {
        return 'string';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }
}