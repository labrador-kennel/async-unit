<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\MockBridge;

use Cspray\Labrador\AsyncUnit\Exception\MockFailureException;
use Cspray\Labrador\AsyncUnit\MockBridge;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Mockery;
use Throwable;

final class MockeryMockBridge implements MockBridge {

    private array $createdMocks = [];

    public function initialize() : void {
        // Mockery requires no initialization
    }

    public function finalize() : void {
        try {
            Mockery::close();
        } catch (Throwable $exception) {
            throw new MockFailureException($exception->getMessage(), previous: $exception);
        }
    }

    public function createMock(string $class) : MockInterface|LegacyMockInterface {
        $mock = Mockery::mock($class);
        $this->createdMocks[] = $mock;
        return $mock;
    }

    public function getAssertionCount(): int {
        $count = 0;
        /** @var MockInterface $createdMock */
        foreach ($this->createdMocks as $createdMock) {
            $count += $createdMock->mockery_getExpectationCount();
        }
        return $count;
    }
}