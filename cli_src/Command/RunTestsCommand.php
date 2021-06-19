<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;

use Cspray\Labrador\AsyncUnit\AsyncUnitFrameworkRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunTestsCommand extends Command {

    public function __construct(
        private AsyncUnitFrameworkRunner $frameworkRunner
    ) {
        parent::__construct('run');
    }

    public function execute(InputInterface $input, OutputInterface $output) : int {
        return $this->frameworkRunner->run() ? Command::SUCCESS : Command::FAILURE;
    }

}