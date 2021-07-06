<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\ByteStream\OutputStream;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\ProcessingFinishedEvent;
use Cspray\Labrador\Engine;
use Cspray\Labrador\Environment;
use Psr\Log\LoggerInterface;

/**
 * A Facade, traditional OOP definition, to easily run a series of tests based on a Configuration that could be overridden
 * based on the precise context in which the AsyncUnitFrameworkRunner is being used.
 *
 *
 *
 * @package Cspray\Labrador\AsyncUnit
 */
final class AsyncUnitFrameworkRunner {

    public function __construct(
        private Environment $environment,
        private LoggerInterface $logger,
        private ConfigurationFactory $configurationFactory,
        private OutputStream $testResultOutput,
        private ?MockBridgeFactory $mockBridgeFactory = null
    ) {}

    public function run(string $configFile) : bool {
        $injector = (new AsyncUnitApplicationObjectGraph(
            $this->environment,
            $this->logger,
            $this->configurationFactory,
            $this->testResultOutput,
            $configFile,
            $this->mockBridgeFactory
        ))->wireObjectGraph();

        $emitter = $injector->make(EventEmitter::class);
        $hasFailedTests = false;
        $emitter->once(Events::PROCESSING_FINISHED, function(ProcessingFinishedEvent $event) use(&$hasFailedTests) {
            $hasFailedTests = $event->getTarget()->getFailedTestCount() !== 0;
        });

        $injector->execute(Engine::class . '::run');
        return !$hasFailedTests;
    }

}