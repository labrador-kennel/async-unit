<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Promise;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\AsyncUnit\Parser\Parser;
use Cspray\Labrador\AsyncUnit\Parser\ParserResult;
use Cspray\Labrador\AsyncUnitCli\DefaultResultPrinter;
use Cspray\Labrador\Plugin\Pluggable;
use function Amp\call;

final class TestFrameworkApplication extends AbstractApplication {

    private Parser $parser;
    private TestSuiteRunner $testSuiteRunner;
    private array $dirs;
    protected Pluggable $pluginManager;

    public function __construct(
        Pluggable $pluggable,
        Parser $parser,
        TestSuiteRunner $testSuiteRunner,
        array $dirs
    ) {
        parent::__construct($pluggable);
        $this->pluginManager = $pluggable;
        $this->parser = $parser;
        $this->testSuiteRunner = $testSuiteRunner;
        $this->dirs = $dirs;
    }

    protected function doStart() : Promise {
        return call(function() {
            $parserResults = yield $this->parser->parse(...$this->dirs);

            gc_collect_cycles();

            yield $this->loadDynamicPlugins($parserResults);

            yield $this->testSuiteRunner->runTestSuites($parserResults);

        });
    }

    private function loadDynamicPlugins(ParserResult $parserResults) : Promise {
        return call(function() use($parserResults) {
            // This absolutely no good ugly hack is going to be necessary until Labrador Core has support for dynamic Plugin loading
            // Please see https://github.com/labrador-kennel/core/issues/110
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


}