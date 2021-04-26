<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Parser;
use Cspray\Labrador\AsyncUnit\TestFrameworkApplicationObjectGraph;
use Cspray\Labrador\Engine;
use Symfony\Component\Console\Style\SymfonyStyle;

class AsyncUnitFrameworkRunner {

    public function __construct(
        private TestFrameworkApplicationObjectGraph $applicationObjectGraph,
        private string $version
    ) {}

    public function run(array $testDirs, SymfonyStyle $symfonyStyle) : bool {
        $injector = $this->applicationObjectGraph->wireObjectGraph();

        /** @var Parser $parser */
        $parser = $injector->make(Parser::class);
        $parserResults = $parser->parse($testDirs);

        $injector->share($parserResults);

        $emitter = $injector->make(EventEmitter::class);
        $hasFailedTests = false;
        $emitter->once(Events::TEST_PROCESSING_FINISHED_EVENT, function(TestProcessingFinishedEvent $event) use(&$hasFailedTests) {
            $hasFailedTests = $event->getTarget()->getFailureTestCount() !== 0;
        });

        (new DefaultResultPrinter($this->version))->registerEvents($emitter, $symfonyStyle);

        $injector->execute(Engine::class . '::run');
        return !$hasFailedTests;
    }

}