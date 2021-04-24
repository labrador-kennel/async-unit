<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool\Command;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\TestFrameworkApplicationBootstrap;
use Cspray\Labrador\Engine;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use League\CLImate\CLImate;
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
        $directories = [];
        $cwd = getcwd();
        foreach ($input->getArgument('test-dirs') as $testDir) {
            $directories[] = $cwd . '/' . $testDir;
        }
        $injector = (new TestFrameworkApplicationBootstrap($environment, $logger, $directories))->getBootstrappedInjector();

        $emitter = $injector->make(EventEmitter::class);
        $cli = new CLImate();

        $state = new \stdClass();
        $state->cli = $cli;
        $state->hadFailingTests = false;

        $emitter->once(Events::TEST_PROCESSING_FINISHED_EVENT, function() use($state, $cli) {
            if ($state->hadFailingTests) {
                $cli->br()->br()->red()->flank('There were failing tests', '!', 1);
                $cli->border('=');
            }
        });

        $emitter->on(Events::TEST_FAILED_EVENT, function(TestFailedEvent $event) use($state, $emitter, $cli) {
            $state->hadFailingTests = true;
            $state->cli->lightRed()->inline('X');
            $emitter->once(Events::TEST_PROCESSING_FINISHED_EVENT, function() use($cli, $event) {
                $cli->inline($event->getTarget()->getTestCase()::class)->inline('::')
                    ->inline($event->getTarget()->getTestMethod())->out(' failed!');
                if ($event->getTarget()->getFailureException()->isAssertionFailure()) {
                    $cli->out(
                        $event->getTarget()->getFailureException()->getAssertionFailureFile() . 'L' . $event->getTarget()->getFailureException()->getAssertionFailureLine()
                    );
                }
                $cli->br()->tab()->out($event->getTarget()->getFailureException()->getMessage());
                if ($event->getTarget()->getFailureException()->isAssertionFailure()) {
                    $cli->tab()->out($event->getTarget()->getFailureException()->getComparisonDisplay()->toString());
                }
                $cli->br()->out('Stack trace:')->out($event->getTarget()->getFailureException()->getTraceAsString());
                $cli->br()->border('-')->br();
            });
        });

        $emitter->on(Events::TEST_PASSED_EVENT, function() use($state) {
            $state->cli->green()->inline('.');
        });

        $inspirationalMessages = [
            'Let\'s run some asynchronous tests!',
            'Zoom, zoom... here we go!',
            'One Loop to rule them all.',
            'Alright, waking the hamsters up!',
        ];
        $cli->bold()->backgroundBlue()->inline('AsyncUnit')
            ->inline(' ')
            ->lightYellow()->inline('v' . $this->version)
            ->inline(' - ')
            ->out($inspirationalMessages[array_rand($inspirationalMessages)])
            ->br()
            ->inline('Runtime: PHP ')->out(phpversion())
            ->br();

        $injector->execute(Engine::class . '::run');

        return Command::SUCCESS;
    }

}