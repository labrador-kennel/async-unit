<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\ByteStream\OutputBuffer;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\MockBridge\MockeryMockBridge;
use Cspray\Labrador\AsyncUnit\Stub\TestConfiguration;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Log\NullLogger;

class AsyncUnitFrameworkRunnerTest extends PHPUnitTestCase {

    use UsesAcmeSrc;

    public function testSinglePassingTest() {
        $environment = new StandardEnvironment(EnvironmentType::Test());
        $logger = new NullLogger();
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('SingleTest')]);
        $configurationFactory = $this->createMock(ConfigurationFactory::class);
        $configurationFactory->expects($this->once())
            ->method('make')
            ->with('configPath')
            ->willReturn(new Success($configuration));

        $frameworkRunner = new AsyncUnitFrameworkRunner(
            $environment,
            $logger,
            $configurationFactory,
            new OutputBuffer()
        );

        $this->assertTrue($frameworkRunner->run('configPath'));
    }

    public function testFailedAssertionTest() {
        $environment = new StandardEnvironment(EnvironmentType::Test());
        $logger = new NullLogger();
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('FailedAssertion')]);
        $configurationFactory = $this->createMock(ConfigurationFactory::class);
        $configurationFactory->expects($this->once())
            ->method('make')
            ->with('configPath')
            ->willReturn(new Success($configuration));

        $frameworkRunner = new AsyncUnitFrameworkRunner(
            $environment,
            $logger,
            $configurationFactory,
            new OutputBuffer()
        );

        $this->assertFalse($frameworkRunner->run('configPath'));
    }

    public function testSingleMockWithNoAssertion() {
        $environment = new StandardEnvironment(EnvironmentType::Test());
        $logger = new NullLogger();
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('MockeryTestNoAssertion')]);
        $configuration->setMockBridge(MockeryMockBridge::class);
        $configurationFactory = $this->createMock(ConfigurationFactory::class);
        $configurationFactory->expects($this->once())
            ->method('make')
            ->with('configPath')
            ->willReturn(new Success($configuration));

        $frameworkRunner = new AsyncUnitFrameworkRunner(
            $environment,
            $logger,
            $configurationFactory,
            new OutputBuffer()
        );

        $this->assertTrue($frameworkRunner->run('configPath'));
    }

}