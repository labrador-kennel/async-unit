<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Promise;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Cspray\Labrador\AsyncUnitCli\DefaultResultPrinter;
use Cspray\Labrador\Plugin\Pluggable;
use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\Timer;
use stdClass;
use function Amp\call;

final class TestFrameworkApplication extends AbstractApplication {

    private EventEmitter $emitter;
    private Parser $parser;
    private TestSuiteRunner $testSuiteRunner;
    private array $dirs;
    protected Pluggable $pluginManager;

    public function __construct(
        Pluggable $pluggable,
        EventEmitter $emitter,
        Parser $parser,
        TestSuiteRunner $testSuiteRunner,
        array $dirs
    ) {
        parent::__construct($pluggable);
        $this->pluginManager = $pluggable;
        $this->emitter = $emitter;
        $this->parser = $parser;
        $this->testSuiteRunner = $testSuiteRunner;
        $this->dirs = $dirs;
    }

    protected function doStart() : Promise {
        return call(function() {
            $testRunState = new stdClass();
            $testRunState->testsProcessed = 0;
            $testRunState->failedTests = 0;
            $testRunState->disabledTests = 0;
            $testRunState->totalAssertionCount = 0;
            $testRunState->totalAsyncAssertionCount = 0;

            $this->emitter->on(Events::TEST_PROCESSED, function(TestProcessedEvent $testInvokedEvent) use($testRunState) {
                $testRunState->testsProcessed++;
                $testRunState->totalAssertionCount += $testInvokedEvent->getTarget()->getTestCase()->getAssertionCount();
                $testRunState->totalAsyncAssertionCount += $testInvokedEvent->getTarget()->getTestCase()->getAsyncAssertionCount();
                if (TestState::Failed()->equals($testInvokedEvent->getTarget()->getState())) {
                    $testRunState->failedTests++;
                } else if (TestState::Disabled()->equals($testInvokedEvent->getTarget()->getState())) {
                    $testRunState->disabledTests++;
                }
            });

            $parserResults = yield $this->parser->parse(...$this->dirs);

            yield $this->loadDynamicPlugins($parserResults);

            yield $this->emitter->emit(
                new TestProcessingStartedEvent($this->getPreRunSummary($parserResults))
            );

            $timer = new Timer();
            $timer->start();

            yield $this->testSuiteRunner->runTestSuites(...$parserResults->getTestSuiteModels());

            $testRunState->duration = $timer->stop();
            $testRunState->memoryUsage = memory_get_peak_usage(true);

            yield $this->emitter->emit(
                new TestProcessingFinishedEvent($this->getPostRunSummary($testRunState))
            );
        });
    }

    private function loadDynamicPlugins(ParserResult $parserResults) : Promise {
        return call(function() use($parserResults) {
            $reflectedPluginManager = new \ReflectionObject($this->pluginManager);
            $loadPlugin = $reflectedPluginManager->getMethod('loadPlugin');
            $loadPlugin->setAccessible(true);

            $hasResultPrinter = false;
            foreach ($parserResults->getPluginModels() as $pluginModel) {
                yield $loadPlugin->invoke($this->pluginManager, $pluginModel->getPluginClass());
                if (is_subclass_of($pluginModel->getPluginClass(), ResultPrinterPlugin::class)) {
                    $hasResultPrinter = true;
                }
            }

            if (!$hasResultPrinter) {
                yield $loadPlugin->invoke($this->pluginManager, DefaultResultPrinter::class);
            }
        });
    }

    private function getPreRunSummary(ParserResult $parserResult) : PreRunSummary {
        return new class($parserResult) implements PreRunSummary {

            public function __construct(private ParserResult $parserResult) {}

            public function getTestSuiteCount() : int {
                return $this->parserResult->getTestSuiteCount();
            }

            public function getTotalTestCaseCount() : int {
                return $this->parserResult->getTotalTestCaseCount();
            }

            public function getTotalTestCount() : int {
                return $this->parserResult->getTotalTestCount();
            }
        };
    }

    private function getPostRunSummary(stdClass $testRunState) : PostRunSummary {
        return new class($testRunState) implements PostRunSummary {

            public function __construct(private stdClass $testRunState) {}

            public function getAssertionCount() : int {
                return $this->testRunState->totalAssertionCount;
            }

            public function getAsyncAssertionCount() : int {
                return $this->testRunState->totalAsyncAssertionCount;
            }

            public function getTotalTestCount() : int {
                return $this->testRunState->testsProcessed;
            }

            public function getPassedTestCount() : int {
                return $this->getTotalTestCount() - $this->getFailedTestCount() - $this->getDisabledTestCount();
            }

            public function getFailedTestCount() : int {
                return $this->testRunState->failedTests;
            }

            public function getDisabledTestCount() : int {
                return $this->testRunState->disabledTests;
            }

            public function getMemoryUsageInBytes() : int {
                return $this->testRunState->memoryUsage;
            }

            public function getDuration() : Duration {
                return $this->testRunState->duration;
            }
        };
    }

}