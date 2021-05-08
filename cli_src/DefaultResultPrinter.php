<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Amp\ByteStream\OutputStream;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Generator;

final class DefaultResultPrinter {

    /**
     * @var TestFailedEvent[]
     */
    private array $failedTests = [];

    public function __construct(private string $version) {}

    public function registerEvents(EventEmitter $emitter, OutputStream $output) : void {
        $emitter->once(Events::TEST_PROCESSING_STARTED, fn() => $this->testProcessingStarted($output));
        $emitter->on(Events::TEST_PASSED, fn() => $this->testPassed($output));
        $emitter->on(Events::TEST_FAILED, fn($event) => $this->testFailed($event, $output));
        $emitter->once(Events::TEST_PROCESSING_FINISHED, fn($event) => $this->testProcessingFinished($event, $output));
    }

    private function testProcessingStarted(OutputStream $output) : Generator {
        $inspirationalMessages = [
            'Let\'s run some asynchronous tests!',
            'Zoom, zoom... here we go!',
            'One Loop to rule them all.',
            'Alright, waking the hamsters up!',
        ];
        $inspirationalMessage = $inspirationalMessages[array_rand($inspirationalMessages)];
        yield $output->write(sprintf("AsyncUnit v%s - %s\n", $this->version, $inspirationalMessage));
        yield $output->write("\n");
        yield $output->write(sprintf("Runtime: PHP %s\n", phpversion()));
        yield $output->write("\n");
    }

    private function testPassed(OutputStream $output) : Generator {
        yield $output->write('.');
    }

    private function testFailed(TestFailedEvent $failedEvent, OutputStream $output) : Generator {
        $this->failedTests[] = $failedEvent;
        yield $output->write('X');
    }

    private function testProcessingFinished(TestProcessingFinishedEvent $event, OutputStream $output) : Generator {
        yield $output->write("\n\n");
        if ($event->getTarget()->getFailureTestCount() === 0) {
            yield $output->write("OK!\n");
            yield $output->write(sprintf(
                "Tests: %d, Assertions: %d, Async Assertions: %d\n",
                $event->getTarget()->getExecutedTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        } else {
            yield $output->write(sprintf("There was %d failure:\n", $event->getTarget()->getFailureTestCount()));
            yield $output->write("\n");
            foreach ($this->failedTests as $index => $failedTestEvent) {
                yield $output->write(sprintf(
                    "%d) %s::%s\n",
                    $index + 1,
                    $failedTestEvent->getTarget()->getTestCase()::class,
                    $failedTestEvent->getTarget()->getTestMethod()
                ));
                $exception = $failedTestEvent->getTarget()->getException();
                if ($exception instanceof AssertionFailedException) {
                    yield $output->write($exception->getDetailedMessage() . "\n");
                    yield $output->write("\n");
                    yield $output->write(sprintf(
                        "%s:%d\n",
                        $exception->getAssertionFailureFile(),
                        $exception->getAssertionFailureLine()
                    ));
                    yield $output->write("\n");
                } else {
                    yield $output->write(sprintf(
                        "An unexpected %s was thrown in %s on line %d.\n",
                        $exception::class,
                        $exception->getFile(),
                        $exception->getLine()
                    ));
                    yield $output->write("\n");
                    yield $output->write(sprintf("\"%s\"\n", $exception->getMessage()));
                    yield $output->write("\n");
                    yield $output->write($exception->getTraceAsString() . "\n");
                    yield $output->write("\n");
                }
            }

            yield $output->write("FAILURES\n");
            yield $output->write(sprintf(
                "Tests: %d, Failures: %d, Assertions: %d, Async Assertions: %d\n",
                $event->getTarget()->getExecutedTestCount(),
                $event->getTarget()->getFailureTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }
    }

}