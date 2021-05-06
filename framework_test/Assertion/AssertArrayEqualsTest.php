<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;


use Cspray\Labrador\AsyncUnit\Assertion;

class AssertArrayEqualsTest extends AbstractAssertionTestCase {

    /**
     * @dataProvider nonArrayProvider
     */
    public function testBadTypes($value) {
        $this->runBadTypeAssertions($value);
    }

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertArrayEquals($value, $actual);
    }

    protected function getGoodValue() {
        return ['a', 'b', 'c'];
    }

    protected function getBadValue() {
        return ['z', 'x', 'y'];
    }

    protected function getExpectedType() {
        return 'array';
    }

    protected function getInvalidTypeAssertionMessageClass() : string {
        return Assertion\AssertionMessage\InvalidTypeBinaryOperandSummary::class;
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\BinaryOperandSummary::class;
    }
}