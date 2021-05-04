<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use PHPUnit\Framework\TestCase;

class BinaryOperandSummaryTest extends TestCase {

    public function dataProvider() {
        return [
            ['string', 'bar'],
            ['integer', 1],
            ['float', 3.14],
            ['array', [1,2,3]],
            ['object', new \stdClass()],
            ['bool', true],
            ['null', null],
            ['resource', STDOUT]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testToString(string $a, mixed $b) : void {
        $message = new BinaryOperandSummary($a, $b);
        $expected = sprintf(
            'asserting type "string" equals type "%s"', strtolower(gettype($b))
        );
        $this->assertSame($expected, $message->toString());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testToNotString(string $a, mixed $b) : void {
        $message = new BinaryOperandSummary($a, $b) ;
        $expected = sprintf(
            'asserting type "string" does not equal type "%s"', strtolower(gettype($b))
        );
        $this->assertSame($expected, $message->toNotString());
    }

}