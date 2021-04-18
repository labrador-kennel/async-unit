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
class AsyncAssertArrayEqualsTest extends AbstractAsyncAssertionTestCase {

    /**
     * @dataProvider nonArrayProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected) : AsyncAssertion {
        return new AsyncAssertArrayEquals($expected);
    }

    protected function getExpectedValue() {
        return ['generators', 'promises', 'coroutines'];
    }

    protected function getBadValue() {
        return ['blocks', 'io', 'nooooo'];
    }

    protected function getExpectedType() : string {
        return 'array';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }
}