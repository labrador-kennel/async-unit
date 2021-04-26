<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DefaultResultPrinter {

    /**
     * @var TestFailedEvent[]
     */
    private array $failedTests = [];

    public function __construct(private string $version) {}

    public function registerEvents(EventEmitter $emitter, SymfonyStyle $output) : void {
        $emitter->once(Events::TEST_PROCESSING_STARTED_EVENT, fn() => $this->testProcessingStarted($output));
        $emitter->on(Events::TEST_PASSED_EVENT, fn() => $this->testPassed($output));
        $emitter->on(Events::TEST_FAILED_EVENT, fn($event) => $this->testFailed($event, $output));
        $emitter->once(Events::TEST_PROCESSING_FINISHED_EVENT, fn($event) => $this->testProcessingFinished($event, $output));
    }

    private function testProcessingStarted(SymfonyStyle $output) : void {
        $inspirationalMessages = [
            'Let\'s run some asynchronous tests!',
            'Zoom, zoom... here we go!',
            'One Loop to rule them all.',
            'Alright, waking the hamsters up!',
        ];
        $inspirationalMessage = $inspirationalMessages[array_rand($inspirationalMessages)];
        $output->writeln(sprintf('<options=bold>AsyncUnit v%s</> - %s', $this->version, $inspirationalMessage));
        $output->writeln('');
        $output->writeln(sprintf('Runtime: PHP %s', phpversion()));
        $output->writeln('');
    }

    private function testPassed(OutputInterface $output) : void {
        $output->write('<fg=green>.</>');
    }

    private function testFailed(TestFailedEvent $failedEvent, SymfonyStyle $output) : void {
        $this->failedTests[] = $failedEvent;
        $output->write('<fg=red>X</>');
    }

    private function testProcessingFinished(TestProcessingFinishedEvent $event, SymfonyStyle $output) : void {
        $output->newLine(2);
        if ($event->getTarget()->getFailureTestCount() === 0) {
            $output->writeln('<fg=green>OK!</>');
            $output->writeln(sprintf(
                'Tests: %d, Assertions: %d, Async Assertions: %d',
                $event->getTarget()->getExecutedTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        } else {
            $output->writeln(sprintf('There was %d failure:', $event->getTarget()->getFailureTestCount()));
            $output->newLine();
            foreach ($this->failedTests as $index => $failedTestEvent) {
                $output->writeln(sprintf(
                    '%d) %s::%s',
                    $index + 1,
                    $failedTestEvent->getTarget()->getTestCase()::class,
                    $failedTestEvent->getTarget()->getTestMethod()
                ));
                $exception = $failedTestEvent->getTarget()->getFailureException();
                if ($exception instanceof AssertionFailedException) {
                    $output->writeln($failedTestEvent->getTarget()->getFailureException()->getDetailedMessage());
                    $output->newLine();
                    $output->writeln(sprintf(
                        '%s:%d',
                        $failedTestEvent->getTarget()->getFailureException()->getAssertionFailureFile(),
                        $failedTestEvent->getTarget()->getFailureException()->getAssertionFailureLine()
                    ));
                    $output->newLine();
                } else {
                    $output->writeln(sprintf(
                        'An unexpected %s was thrown in %s on line %d.',
                        $exception::class,
                        $exception->getFile(),
                        $exception->getLine()
                    ));
                    $output->newLine();
                    $output->writeln(sprintf('"%s"', $exception->getMessage()));
                    $output->newLine();
                    $output->writeln($exception->getTraceAsString());
                    $output->newLine();
                }
            }

            $output->writeln('<fg=red>FAILURES</>');
            $output->writeln(sprintf(
                'Tests: %d, Failures: %d, Assertions: %d, Async Assertions: %d',
                $event->getTarget()->getExecutedTestCount(),
                $event->getTarget()->getFailureTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }
    }

}