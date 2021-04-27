<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool\Command;

use Cspray\Labrador\AsyncUnit\CliTool\AsyncUnitFrameworkRunner;
use Cspray\Labrador\AsyncUnit\CliTool\ConfigurationFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunTestsFromConfigurationCommand extends Command {

    protected static $defaultName = 'run-config';

    public function __construct(
        private AsyncUnitFrameworkRunner $frameworkRunner,
        private ConfigurationFactory $configurationFactory,
        private string $configFile
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output) : int {
        if (!is_file($this->configFile)) {
            $output->writeln(sprintf(
                '<error>Unable to execute async-unit from a configuration file! Nothing found at "%s".</error>',
                $this->configFile
            ));
            return Command::FAILURE;
        }

        $configuration = $this->configurationFactory->make($this->configFile);

        $isOk = $this->frameworkRunner->run($configuration->getTestDirectories(), new SymfonyStyle($input, $output));

        return $isOk ? Command::SUCCESS : Command::FAILURE;
    }

}