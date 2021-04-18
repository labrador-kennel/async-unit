<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool\Command;

use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\TestFrameworkApplicationObjectGraph;
use Cspray\Labrador\Engine;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunTestsCommand extends Command {

    private string $version;

    public function __construct(string $version) {
        parent::__construct('run');
        $this->version = substr($version, 0, 7);
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
        $environment = new StandardEnvironment(EnvironmentType::Development());
        $logger = new NullLogger();
        $injector = (new TestFrameworkApplicationObjectGraph($environment, $logger))->wireObjectGraph();

        $directories = [];
        $cwd = getcwd();
        foreach ($input->getArgument('test-dirs') as $testDir) {
            $directories[] = $cwd . '/' . $testDir;
        }
        $application = $injector->make(Application::class, [
            ':testDirectories' => $directories
        ]);

        $emitter = $injector->make(EventEmitter::class);

        $emitter->on(Events::TEST_FAILED_EVENT, function() use($output) {
            $output->write('X');
        });

        $emitter->on(Events::TEST_PASSED_EVENT, function() use($output) {
            $output->write('.');
        });

        $inspirationalMessages = [
            'Let\'s run some asynchronous tests!',
            'Zoom, zoom... here we go!',
            'One Loop to rule them all.',
            'Alright, waking the hamsters up!',

        ];
        $output->write('AsyncUnit v' . $this->version . ' - ');
        $output->writeln($inspirationalMessages[array_rand($inspirationalMessages)]);
        $output->writeln('');

        $injector->make(Engine::class)->run($application);

        $output->writeln('');

        return Command::SUCCESS;
    }

}