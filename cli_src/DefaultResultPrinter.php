<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Amp\ByteStream\OutputStream;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestDisabledEvent;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\ResultPrinterPlugin;
use Cspray\Labrador\StyledByteStream\TerminalOutputStream;
use Generator;
use SebastianBergmann\Timer\ResourceUsageFormatter;

final class DefaultResultPrinter implements ResultPrinterPlugin {

    /**
     * @var TestFailedEvent[]
     */
    private array $failedTests = [];

    /**
     * @var TestDisabledEvent[]
     */
    private array $disabledTests = [];

    public function __construct(private string $version) {}

    public function registerEvents(EventEmitter $emitter, OutputStream $output) : void {
        $output = new TerminalOutputStream($output);
        $successOutput = $output->green();
        $failedOutput = $output->red();
        $disabledOutput = $output->yellow();
        $emitter->once(Events::TEST_PROCESSING_STARTED, fn() => $this->testProcessingStarted($output));
        $emitter->on(Events::TEST_PASSED, fn() => $this->testPassed($successOutput));
        $emitter->on(Events::TEST_FAILED, fn($event) => $this->testFailed($event, $failedOutput));
        $emitter->on(Events::TEST_DISABLED, fn($event) => $this->testDisabled($event, $disabledOutput));
        $emitter->once(Events::TEST_PROCESSING_FINISHED, fn($event) => $this->testProcessingFinished($event, $output));
    }

    private function testProcessingStarted(TerminalOutputStream $output) : Generator {
        $inspirationalMessages = [
            'Let\'s run some asynchronous tests!',
            'Zoom, zoom... here we go!',
            'One Loop to rule them all.',
            'Alright, waking the hamsters up!',
        ];
        $inspirationalMessage = $inspirationalMessages[array_rand($inspirationalMessages)];
        yield $output->writeln(sprintf("AsyncUnit v%s - %s\n", $this->version, $inspirationalMessage));
        yield $output->writeln(sprintf("Runtime: PHP %s\n", phpversion()));
    }

    private function testPassed(TerminalOutputStream $output) : Generator {
        yield $output->write('.');
    }

    private function testDisabled(TestDisabledEvent $disabledEvent, OutputStream $output) : Generator {
        $this->disabledTests[] = $disabledEvent;
        yield $output->write('D');
    }

    private function testFailed(TestFailedEvent $failedEvent, OutputStream $output) : Generator {
        $this->failedTests[] = $failedEvent;
        yield $output->write('X');
    }

    private function testProcessingFinished(TestProcessingFinishedEvent $event, TerminalOutputStream $output) : Generator {
        yield $output->br(2);
        yield $output->writeln((new ResourceUsageFormatter())->resourceUsage($event->getTarget()->getDuration()));
        yield $output->br();
        if ($event->getTarget()->getFailedTestCount() > 0) {
            yield $output->writeln(sprintf("There was %d failure:\n", $event->getTarget()->getFailedTestCount()));
            foreach ($this->failedTests as $index => $failedTestEvent) {
                yield $output->writeln(sprintf(
                    "%d) %s::%s",
                    $index + 1,
                    $failedTestEvent->getTarget()->getTestCase()::class,
                    $failedTestEvent->getTarget()->getTestMethod()
                ));
                $exception = $failedTestEvent->getTarget()->getException();
                if ($exception instanceof AssertionFailedException) {
                    yield $output->writeln($exception->getDetailedMessage());
                    yield $output->br();
                    yield $output->writeln(sprintf(
                        "%s:%d",
                        $exception->getAssertionFailureFile(),
                        $exception->getAssertionFailureLine()
                    ));
                    yield $output->br();
                } else if ($exception instanceof TestFailedException) {
                    yield $output->br();
                    yield $output->writeln("Test failure message:");
                    yield $output->br();
                    yield $output->writeln($exception->getMessage());
                    yield $output->br();
                    yield $output->writeln($exception->getTraceAsString());
                    yield $output->br();
                } else {
                    yield $output->writeln(sprintf(
                        "An unexpected %s was thrown in %s on line %d.",
                        $exception::class,
                        $exception->getFile(),
                        $exception->getLine()
                    ));
                    yield $output->br();
                    yield $output->writeln(sprintf("\"%s\"", $exception->getMessage()));
                    yield $output->br();
                    yield $output->writeln($exception->getTraceAsString());
                    yield $output->br();
                }
            }

            yield $output->write("FAILURES\n");
            yield $output->write(sprintf(
                "Tests: %d, Failures: %d, Assertions: %d, Async Assertions: %d\n",
                $event->getTarget()->getTotalTestCount(),
                $event->getTarget()->getFailedTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }

        if ($event->getTarget()->getDisabledTestCount() > 0) {
            yield $output->write(sprintf("There was %d disabled test:\n", $event->getTarget()->getDisabledTestCount()));
            yield $output->write("\n");
            foreach ($this->disabledTests as $index => $disabledEvent) {
                yield $output->write(sprintf(
                    "%d) %s::%s\n",
                    $index + 1,
                    $disabledEvent->getTarget()->getTestCase()::class,
                    $disabledEvent->getTarget()->getTestMethod()
                ));
            }
            yield $output->write("\n");
            yield $output->write(sprintf(
                "Tests: %d, Disabled Tests: %d, Assertions: %d, Async Assertions: %d\n",
                $event->getTarget()->getTotalTestCount(),
                $event->getTarget()->getDisabledTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }

        if ($event->getTarget()->getTotalTestCount() === $event->getTarget()->getPassedTestCount()) {
            yield $output->write("OK!\n");
            yield $output->write(sprintf(
                "Tests: %d, Assertions: %d, Async Assertions: %d\n",
                $event->getTarget()->getTotalTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }
    }
}