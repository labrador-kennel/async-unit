<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MockeryTestNoAssertion;

use Amp\Success;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;
use Generator;
use Mockery\MockInterface;

class MyTestCase extends TestCase {

    #[Test]
    public function checkMockExpectations() : Generator {
        /** @var MockInterface $mock */
        $mock = $this->mocks()->createMock(Application::class);

        $mock->expects()->start()->andReturn(new Success());

        yield $mock->start();
    }

}