<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Promise;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\AsyncUnit\Exception\InvalidConfigurationException;
use Cspray\Labrador\AsyncUnit\Parser\Parser;
use Cspray\Labrador\AsyncUnit\Parser\ParserResult;
use Cspray\Labrador\Plugin\Pluggable;
use ReflectionObject;
use function Amp\call;

final class AsyncUnitApplication extends AbstractApplication {

    public const VERSION = '0.6.0-dev';

    private Pluggable $pluginManager;
    private ConfigurationValidator $configurationValidator;
    private ConfigurationFactory $configurationFactory;
    private Parser $parser;
    private TestSuiteRunner $testSuiteRunner;
    private string $configFilePath;

    public function __construct(
        Pluggable $pluggable,
        ConfigurationValidator $configurationValidator,
        ConfigurationFactory $configurationFactory,
        Parser $parser,
        TestSuiteRunner $testSuiteRunner,
        string $configFilePath
    ) {
        parent::__construct($pluggable);
        $this->pluginManager = $pluggable;
        $this->configurationFactory = $configurationFactory;
        $this->configurationValidator = $configurationValidator;
        $this->parser = $parser;
        $this->testSuiteRunner = $testSuiteRunner;
        $this->configFilePath = $configFilePath;
    }

    protected function doStart() : Promise {
        return call(function() {
            $configuration = yield $this->configurationFactory->make($this->configFilePath);
            yield $this->validateConfiguration($configuration);
            $parserResults = yield $this->parser->parse($configuration->getTestDirectories());

            gc_collect_cycles();

            yield $this->loadDynamicPlugins($configuration, $parserResults);

            yield $this->testSuiteRunner->runTestSuites($parserResults);
        });
    }

    private function validateConfiguration(Configuration $configuration) : Promise {
        return call(function() use($configuration) {
            /** @var ConfigurationValidationResults $validationResults */
            $validationResults = yield $this->configurationValidator->validate($configuration);
            if (!$validationResults->isValid()) {
                $firstLine = sprintf(
                    "The configuration at path \"%s\" has the following errors:\n\n",
                    $this->configFilePath
                );
                $errorList = join(
                    PHP_EOL,
                    array_map(fn(string $msg) => "- $msg", $validationResults->getValidationErrors())
                );
                $lastLine = "\n\nPlease fix the errors listed above and try running your tests again.";

                throw new InvalidConfigurationException(sprintf('%s%s%s', $firstLine, $errorList, $lastLine));
            }
        });
    }

    private function loadDynamicPlugins(Configuration $configuration, ParserResult $parserResults) : Promise {
        return call(function() use($configuration, $parserResults) {
            // This absolutely no good ugly hack is going to be necessary until Labrador Core has support for dynamic Plugin loading
            // Please see https://github.com/labrador-kennel/core/issues/110
            $reflectedPluginManager = new ReflectionObject($this->pluginManager);
            $loadPlugin = $reflectedPluginManager->getMethod('loadPlugin');
            $loadPlugin->setAccessible(true);

            yield $loadPlugin->invoke($this->pluginManager, $configuration->getResultPrinterClass());
        });
    }


}