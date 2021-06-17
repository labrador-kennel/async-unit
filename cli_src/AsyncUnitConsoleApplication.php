<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Cspray\Labrador\AsyncUnitCli\Command\RunTestsCommand;
use Cspray\Labrador\AsyncUnitCli\Command\RunTestsFromConfigurationCommand;
use Cspray\Labrador\AsyncUnit\AsyncUnitApplicationObjectGraph;
use Symfony\Component\Console\Application as ConsoleApplication;

final class AsyncUnitConsoleApplication extends ConsoleApplication {

    public function __construct(
        private ConfigurationFactory $configurationFactory,
        private AsyncUnitApplicationObjectGraph $applicationObjectGraph,
        private string $cwd,
        string $version
    ) {
        parent::__construct('AsyncUnit', $version);
        $this->registerCommands();
    }

    private function registerCommands() {
        $frameworkRunner = new AsyncUnitFrameworkRunner($this->applicationObjectGraph, $this->getVersion());
        $this->add(new RunTestsFromConfigurationCommand(
            $frameworkRunner,
            $this->configurationFactory,
            $this->cwd . '/async-unit.json'
        ));
        $this->setDefaultCommand('run-config');

        $this->add(new RunTestsCommand(
            $frameworkRunner,
            $this->cwd
        ));
    }

}