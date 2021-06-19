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
        private OutputStream $testResultOutput
    ) {}

    private function getConfiguration(?string $configFile) : Configuration {
        if (isset($this->configurationOverride)) {
            return $this->configurationOverride;
        }

        if (!isset($configFile)) {
            return $this->getDefaultConfiguration();
        }

        return $this->configurationFactory->make($configFile);
    }

    public function run(string $configFile = null) : bool {
        $injector = (new AsyncUnitApplicationObjectGraph(
            $this->environment,
            $this->logger,
            $this->getConfiguration($configFile),
            $this->testResultOutput
        ))->wireObjectGraph();

        $emitter = $injector->make(EventEmitter::class);
        $hasFailedTests = false;
        $emitter->once(Events::PROCESSING_FINISHED, function(ProcessingFinishedEvent $event) use(&$hasFailedTests) {
            $hasFailedTests = $event->getTarget()->getFailedTestCount() !== 0;
        });

        $injector->execute(Engine::class . '::run');
        return !$hasFailedTests;
    }

    private function getDefaultConfiguration() : Configuration {
        return new class implements Configuration {

            public function getTestDirectories(): array {
                return [getcwd()];
            }

            public function getPlugins(): array {
                return [];
            }

            public function getResultPrinterClass(): string {
                return '';
            }
        };
    }

}