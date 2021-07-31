<?php

namespace Cspray\Labrador\AsyncUnit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanBeDisabledTraitTest extends TestCase {

    private CanBeDisabledTrait|MockObject $subject;

    public function setUp(): void {
        $this->subject = $this->getMockForTrait(CanBeDisabledTrait::class);
    }

    public function testIsDisabledFalseIfNoDeterminator() : void {
        $this->assertFalse($this->subject->getDisabledDeterminator()->isDisabled());
    }

    public function testGetReasonIsNullIfNoDeterminator() : void {
        $this->assertNull($this->subject->getDisabledDeterminator()->getReason());
    }

    public function testIsDisabledDelegatesToDeterminator() : void {
        $determinator = $this->getMockBuilder(DisabledDeterminator::class)->getMock();
        $this->subject->setDisabledDeterminator($determinator);

        $this->assertSame($determinator, $this->subject->getDisabledDeterminator());
    }

}