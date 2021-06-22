<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\ByteStream\OutputStream;
use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncUnit\Parser\Parser;
use Cspray\Labrador\AsyncUnit\Parser\StaticAnalysisParser;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\CoreApplicationObjectGraph;
use Cspray\Labrador\Environment;
use Psr\Log\LoggerInterface;

final class AsyncUnitApplicationObjectGraph extends CoreApplicationObjectGraph {

    public function __construct(
        Environment $environment,
        LoggerInterface $logger,
        private ConfigurationFactory $configurationFactory,
        private OutputStream $testResultOutput,
        private string $configFilePath,
        private ?MockBridgeFactory $mockBridgeFactory = null,
    ) {
        parent::__construct($environment, $logger);
    }

    public function wireObjectGraph() : Injector {
        $injector = parent::wireObjectGraph();

        $customAssertionContext = (new \ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $injector->share($customAssertionContext);

        $mockBridgeFactory = $this->mockBridgeFactory ?? new SupportedMockBridgeFactory($injector);
        $injector->share(StaticAnalysisParser::class);
        $injector->alias(Parser::class, StaticAnalysisParser::class);
        $injector->share(TestSuiteRunner::class);
        $injector->share(new ShuffleRandomizer());
        $injector->alias(Randomizer::class, ShuffleRandomizer::class);
        $injector->share(ConfigurationValidator::class);
        $injector->alias(ConfigurationValidator::class, AsyncUnitConfigurationValidator::class);
        $injector->share($this->configurationFactory);
        $injector->alias(ConfigurationFactory::class, get_class($this->configurationFactory));
        $injector->share($mockBridgeFactory);
        $injector->alias(MockBridgeFactory::class, get_class($mockBridgeFactory));

        $injector->share(Application::class);
        $injector->alias(Application::class, AsyncUnitApplication::class);
        $injector->define(AsyncUnitApplication::class, [':configFilePath' => $this->configFilePath]);
        $injector->prepare(Application::class, function(Application $application) use($injector) {
            $application->registerPluginLoadHandler(CustomAssertionPlugin::class, function(CustomAssertionPlugin $plugin) use($injector) {
                yield $injector->execute([$plugin, 'registerCustomAssertions']);
            });
            $application->registerPluginLoadHandler(ResultPrinterPlugin::class, function(ResultPrinterPlugin $resultPrinterPlugin) use($injector) {
                $injector->execute([$resultPrinterPlugin, 'registerEvents'], [':output' => $this->testResultOutput]);
            });
        });

        return $injector;
    }
}