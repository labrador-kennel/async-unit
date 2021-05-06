<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Cspray\Labrador\AsyncUnit\TestOutput;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyConsoleTerminalOutput implements TestOutput {

    public function __construct(private OutputInterface $output) {}

    public function write(string $text) : void {
        $this->output->write($text);
    }

    public function writeln(string $text) : void {
        $this->output->writeln($text);
    }
}