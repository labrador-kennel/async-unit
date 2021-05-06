<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\TestOutput;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;

final class DefaultResultPrinter {

    /**
     * @var TestFailedEvent[]
     */
    private array $failedTests = [];

    public function __construct(private string $version) {}

    public function registerEvents(EventEmitter $emitter, TestOutput $output) : void {
        $emitter->once(Events::TEST_PROCESSING_STARTED, fn() => $this->testProcessingStarted($output));
        $emitter->on(Events::TEST_PASSED, fn() => $this->testPassed($output));
        $emitter->on(Events::TEST_FAILED, fn($event) => $this->testFailed($event, $output));
        $emitter->once(Events::TEST_PROCESSING_FINISHED, fn($event) => $this->testProcessingFinished($event, $output));
    }

    private function testProcessingStarted(TestOutput $output) : void {
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

    private function testPassed(TestOutput $output) : void {
        $output->write('<fg=green>.</>');
    }

    private function testFailed(TestFailedEvent $failedEvent, TestOutput $output) : void {
        $this->failedTests[] = $failedEvent;
        $output->write('<fg=red>X</>');
    }

    private function testProcessingFinished(TestProcessingFinishedEvent $event, TestOutput $output) : void {
        $output->writeln('');
        $output->writeln('');
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
            $output->writeln('');
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
                    $output->writeln('');
                    $output->writeln(sprintf(
                        '%s:%d',
                        $failedTestEvent->getTarget()->getFailureException()->getAssertionFailureFile(),
                        $failedTestEvent->getTarget()->getFailureException()->getAssertionFailureLine()
                    ));
                    $output->writeln('');
                } else {
                    $output->writeln(sprintf(
                        'An unexpected %s was thrown in %s on line %d.',
                        $exception::class,
                        $exception->getFile(),
                        $exception->getLine()
                    ));
                    $output->writeln('');
                    $output->writeln(sprintf('"%s"', $exception->getMessage()));
                    $output->writeln('');
                    $output->writeln($exception->getTraceAsString());
                    $output->writeln('');
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