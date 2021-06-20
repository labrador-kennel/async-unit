<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;

use Amp\File\Driver as FileDriver;
use Amp\Loop;
use Cspray\Labrador\AsyncUnitCli\DefaultResultPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateConfigurationCommand extends AbstractCommand {

    public function __construct(
        private FileDriver $fileDriver,
        private string $configPath
    ) {
        parent::__construct();
    }

    protected function configure() {
        $this->addOption('file', mode: InputOption::VALUE_REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output) : int {
        $message = '';
        Loop::run(function() use($input, $output, &$message) {
            $filePath = $input->getOption('file') ?? $this->configPath;
            if (yield $this->fileDriver->isfile($filePath)) {
                $output->writeln(sprintf(
                    'A configuration already exists at %s.',
                    $filePath
                ));
                $replace = $this->confirm(
                    $input,
                    $output,
                    'Would you like to create a new configuration?'
                );
                if (!$replace) {
                    $message = 'Ok! No configuration was created.';
                    return;
                } else {
                    $output->writeln(sprintf(
                        'Previous configuration moved to %s.',
                        $filePath . '.bak'
                    ));
                    yield $this->fileDriver->rename($filePath, $filePath . '.bak');
                }
            }
            $config = [
                'testDirectories' => ['./tests'],
                'resultPrinter' => DefaultResultPrinter::class,
                'plugins' => []
            ];
            yield $this->fileDriver->put($filePath, json_encode($config, JSON_PRETTY_PRINT));
            $message = sprintf('Ok! Configuration created at %s.', $filePath);
        });
        $output->writeln($message);
        return Command::SUCCESS;
    }

    protected function getCommandName(): string {
        return 'config:generate';
    }
}