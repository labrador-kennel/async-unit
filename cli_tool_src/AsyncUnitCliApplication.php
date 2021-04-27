<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool;

use Cspray\Labrador\AsyncUnit\CliTool\Command\RunTestsCommand;
use Cspray\Labrador\AsyncUnit\CliTool\Command\RunTestsFromConfigurationCommand;
use Cspray\Labrador\AsyncUnit\TestFrameworkApplicationObjectGraph;
use Symfony\Component\Console\Application as ConsoleApplication;

class AsyncUnitCliApplication extends ConsoleApplication {

    public function __construct(
        private ConfigurationFactory $configurationFactory,
        private TestFrameworkApplicationObjectGraph $applicationObjectGraph,
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