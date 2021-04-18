<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
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

    protected function getAssertion($expected) : AsyncAssertion {
        return new AsyncAssertStringEquals($expected);
    }

    protected function getExpectedValue() {
        return 'async unit';
    }

    protected function getBadValue() {
        return 'blocking code';
    }

    protected function getExpectedType() : string {
        return 'string';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }
}