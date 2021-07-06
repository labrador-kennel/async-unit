<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;

use Amp\Success;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateConfigurationCommandTest extends BaseCommandTest {

    public function testGenerateFileDoesNotExist() {
        $application = $this->createApplication($configPath = __DIR__ . '/async-unit.json');
        $this->filesystem->expects($this->once())
            ->method('isfile')
            ->with($configPath)
            ->willReturn(new Success(false));

        $this->filesystem->expects($this->once())
            ->method('put')
            ->with($configPath, $this->getDefaultConfigurationJson())
            ->willReturn(new Success());

        $command = $application->find('config:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $expected = <<<shell
Ok! Configuration created at $configPath.

shell;

        $this->assertSame($expected, $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testGenerateFileDoesNotExistOverridesDefaultLocation() {
        $application = $this->createApplication($configPath = __DIR__ . '/async-unit.json');
        $this->filesystem->expects($this->once())
            ->method('isfile')
            ->with('/my/overridden/path')
            ->willReturn(new Success(false));

        $this->filesystem->expects($this->once())
            ->method('put')
            ->with('/my/overridden/path', $this->getDefaultConfigurationJson())
            ->willReturn(new Success());

        $command = $application->find('config:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--file' => '/my/overridden/path'
        ]);
        $expected = <<<shell
Ok! Configuration created at /my/overridden/path.

shell;

        $this->assertSame($expected, $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testGenerateFileDoesExistNoReplace() {
        $application = $this->createApplication($configPath = __DIR__ . '/async-unit.json');
        $this->filesystem->expects($this->once())
            ->method('isfile')
            ->with($configPath)
            ->willReturn(new Success(true));

        $this->filesystem->expects($this->never())->method('put');

        $command = $application->find('config:generate');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([""]);
        $commandTester->execute([]);

        $expected = <<<shell
A configuration already exists at $configPath.
Would you like to create a new configuration? (y/N) Ok! No configuration was created.

shell;

        $this->assertSame($expected, $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testGenerateFilesDoesExistWillReplace() {
        $application = $this->createApplication($configPath = __DIR__ . '/async-unit.json');
        $this->filesystem->expects($this->once())
            ->method('isfile')
            ->with($configPath)
            ->willReturn(new Success(true));

        $this->filesystem->expects($this->once())
            ->method('rename')
            ->with($configPath, $configPath . '.bak')
            ->willReturn(new Success());

        $this->filesystem->expects($this->once())
            ->method('put')
            ->with($configPath, $this->getDefaultConfigurationJson())
            ->willReturn(new Success());

        $command = $application->find('config:generate');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(["y"]);
        $commandTester->execute([]);

        $expected = <<<shell
A configuration already exists at $configPath.
Would you like to create a new configuration? (y/N) Previous configuration moved to $configPath.bak.
Ok! Configuration created at $configPath.

shell;

        $this->assertSame($expected, $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }
}