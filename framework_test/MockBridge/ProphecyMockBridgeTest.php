<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\MockBridge;

use Amp\Success;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncUnit\Exception\MockFailureException;
use PHPUnit\Framework\TestCase;

class ProphecyMockBridgeTest extends TestCase {

    public function testMockWithBadPredictions() {
        $subject = new ProphecyMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Application::class);

        $mock->start()->shouldBeCalled()->willReturn(new Success());

        $this->expectException(MockFailureException::class);

        $subject->finalize();
    }

    public function testMockWithGoodPredictions() {
        $this->expectNotToPerformAssertions();
        $subject = new ProphecyMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Application::class);

        $mock->start()->shouldBeCalled()->willReturn(new Success());

        $mock->reveal()->start();

        $subject->finalize();
    }

    public function testMockAssertionCount() {
        $subject = new ProphecyMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Application::class);

        $mock->start()->shouldBeCalled()->willReturn(new Success());

        $secondMock = $subject->createMock(Application::class);
        $secondMock->start()->shouldBeCalled()->willReturn(new Success());

        $this->assertSame(2, $subject->getAssertionCount());
    }

}