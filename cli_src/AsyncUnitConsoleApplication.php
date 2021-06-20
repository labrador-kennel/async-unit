<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Amp\ByteStream\OutputStream;
use Amp\File\Driver as FileDriver;
use Cspray\Labrador\AsyncUnit\AsyncUnitApplication;
use Cspray\Labrador\AsyncUnit\AsyncUnitFrameworkRunner;
use Cspray\Labrador\AsyncUnit\ConfigurationFactory;
use Cspray\Labrador\AsyncUnitCli\Command\GenerateConfigurationCommand;
use Cspray\Labrador\AsyncUnitCli\Command\RunTestsCommand;
use Cspray\Labrador\Environment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

final class AsyncUnitConsoleApplication extends ConsoleApplication {

    public function __construct(
        private Environment $environment,
        private LoggerInterface $logger,
        private FileDriver $fileDriver,
        private ConfigurationFactory $configurationFactory,
        private OutputStream $testResultOutput,
        private string $configPath
    ) {
        parent::__construct('AsyncUnit', AsyncUnitApplication::VERSION);
        $this->registerCommands();
    }

    private function registerCommands() {
        $frameworkRunner = new AsyncUnitFrameworkRunner(
            $this->environment,
            $this->logger,
            $this->configurationFactory,
            $this->testResultOutput
        );
        $this->add(new RunTestsCommand($this->fileDriver, $frameworkRunner, $this->configPath));
        $this->add(new GenerateConfigurationCommand($this->fileDriver, $this->configPath));
        $this->setDefaultCommand('run');

    }

}