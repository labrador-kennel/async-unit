<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool\Command;

use Cspray\Labrador\AsyncUnit\CliTool\AsyncUnitFrameworkRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunTestsCommand extends Command {

    public function __construct(
        private AsyncUnitFrameworkRunner $frameworkRunner,
        private string $cwd
    ) {
        parent::__construct('run');
    }

    protected function configure() {
        $this->setDescription('Run a set of tests on an Amphp Loop');
        $this->addArgument('test-dirs', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The directories holding your tests');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $directories = [];
        foreach ($input->getArgument('test-dirs') as $testDir) {
            $directories[] = $this->cwd . '/' . $testDir;
        }

        $isOk = $this->frameworkRunner->run($directories, new SymfonyStyle($input, $output));
        return $isOk ? Command::SUCCESS : Command::FAILURE;
    }

}