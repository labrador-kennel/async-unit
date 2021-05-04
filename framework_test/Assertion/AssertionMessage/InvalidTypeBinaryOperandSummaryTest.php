<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use PHPUnit\Framework\TestCase;

class InvalidTypeBinaryOperandSummaryTest extends TestCase {

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
    public function testToString(string $a, mixed $b) {
        $message = new InvalidTypeBinaryOperandSummary($a, $b);
        $expected = sprintf(
            'asserting actual value with type "%s" is comparable to type "%s"',
            strtolower(gettype($b)),
            strtolower(gettype($a))
        );
        $this->assertSame($expected, $message->toString());
    }



}