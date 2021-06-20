<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;


use Amp\ByteStream\OutputBuffer;
use Amp\File\Driver as FileDriver;
use Cspray\Labrador\AsyncUnit\JsonConfigurationFactory;
use Cspray\Labrador\AsyncUnitCli\AsyncUnitConsoleApplication;
use Cspray\Labrador\AsyncUnitCli\DefaultResultPrinter;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

abstract class BaseCommandTest extends TestCase {

    protected FileDriver|MockObject $filesystem;

    protected OutputBuffer $testResultBuffer;

    protected function createApplication(string $configPath, FileDriver $mockFileDriver = null) : AsyncUnitConsoleApplication {
        $this->filesystem = $mockFileDriver ?? $this->createMock(FileDriver::class);
        return new AsyncUnitConsoleApplication(
            new StandardEnvironment(EnvironmentType::Test()),
            new NullLogger(),
            $this->filesystem,
            new JsonConfigurationFactory(),
            $this->testResultBuffer = new OutputBuffer(),
            $configPath
        );
    }

    protected function getDefaultConfigurationJson() : string {
        $expected = [
            'testDirectories' => ['./tests'],
            'resultPrinter' => DefaultResultPrinter::class,
            'plugins' => []
        ];
        return json_encode($expected, JSON_PRETTY_PRINT);
    }

}