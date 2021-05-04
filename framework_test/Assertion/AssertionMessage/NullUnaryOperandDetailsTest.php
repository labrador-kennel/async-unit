<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use PHPUnit\Framework\TestCase;

class NullUnaryOperandDetailsTest extends TestCase {

    public function dataProvider() : array {
        return [
            ['foo'],
            [1],
            [3.14],
            [true],
            [new \stdClass()],
            [STDOUT],
            [[1,2,3]],
            [null]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testToString(mixed $actual) {
        $message = new NullUnaryOperandDetails($actual);
        $expectedDetails = var_export($actual, true);
        if (is_null($actual)) {
            $expectedDetails = strtolower($expectedDetails);
        }
        $expected = sprintf('comparing %s is null', $expectedDetails);
        $this->assertSame($expected, $message->toString());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testToNotString(mixed $actual) {
        $message = new NullUnaryOperandDetails($actual);
        $expectedDetails = var_export($actual, true);
        if (is_null($actual)) {
            $expectedDetails = strtolower($expectedDetails);
        }
        $expected = sprintf('comparing %s is not null', $expectedDetails);
        $this->assertSame($expected, $message->toNotString());
    }

}