<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Amp\ByteStream\OutputStream;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\TestFrameworkApplication;
use Cspray\Labrador\AsyncUnit\TestFrameworkApplicationObjectGraph;
use Cspray\Labrador\AsyncUnit\ResultPrinterPlugin;
use Cspray\Labrador\Engine;

final class AsyncUnitFrameworkRunner {

    public function __construct(
        private TestFrameworkApplicationObjectGraph $applicationObjectGraph,
        private string $version
    ) {}

    public function run(array $testDirs, OutputStream $terminalOutput) : bool {
        $injector = $this->applicationObjectGraph->wireObjectGraph();

        $emitter = $injector->make(EventEmitter::class);
        $hasFailedTests = false;
        $emitter->once(Events::TEST_PROCESSING_FINISHED, function(TestProcessingFinishedEvent $event) use(&$hasFailedTests) {
            $hasFailedTests = $event->getTarget()->getFailedTestCount() !== 0;
        });

        /** @var TestFrameworkApplication $app */
        $app = $injector->make(Application::class, [':dirs' => $testDirs]);
        $app->registerPluginLoadHandler(ResultPrinterPlugin::class, function(ResultPrinterPlugin $resultPrinterPlugin) use($emitter, $terminalOutput) {
            $resultPrinterPlugin->registerEvents($emitter, $terminalOutput);
        });
        $injector->define(DefaultResultPrinter::class, [':version' => $this->version]);

        $injector->execute(Engine::class . '::run');
        return !$hasFailedTests;
    }

}