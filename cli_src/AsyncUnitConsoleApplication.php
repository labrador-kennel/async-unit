<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Amp\ByteStream\OutputStream;
use Cspray\Labrador\AsyncUnit\AsyncUnitApplication;
use Cspray\Labrador\AsyncUnit\AsyncUnitFrameworkRunner;
use Cspray\Labrador\AsyncUnit\ConfigurationFactory;
use Cspray\Labrador\AsyncUnitCli\Command\RunTestsCommand;
use Cspray\Labrador\Environment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

final class AsyncUnitConsoleApplication extends ConsoleApplication {

    public function __construct(
        private Environment $environment,
        private LoggerInterface $logger,
        private ConfigurationFactory $configurationFactory,
        private OutputStream $testResultOutput
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
        $this->add(new RunTestsCommand($frameworkRunner));
        $this->setDefaultCommand('run');
    }

}