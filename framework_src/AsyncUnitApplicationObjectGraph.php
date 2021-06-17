<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncUnit\Parser\Parser;
use Cspray\Labrador\AsyncUnit\Parser\StaticAnalysisParser;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\CoreApplicationObjectGraph;

final class AsyncUnitApplicationObjectGraph extends CoreApplicationObjectGraph {

    public function wireObjectGraph() : Injector {
        $injector = parent::wireObjectGraph();

        $customAssertionContext = (new \ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $injector->share($customAssertionContext);

        $injector->share(StaticAnalysisParser::class);
        $injector->alias(Parser::class, StaticAnalysisParser::class);
        $injector->share(TestSuiteRunner::class);
        $injector->share(new ShuffleRandomizer());
        $injector->alias(Randomizer::class, ShuffleRandomizer::class);

        $injector->share(Application::class);
        $injector->alias(Application::class, AsyncUnitApplication::class);
        $injector->prepare(Application::class, function(Application $application) use($customAssertionContext) {
            $application->registerPluginLoadHandler(CustomAssertionPlugin::class, function(CustomAssertionPlugin $plugin) use($customAssertionContext) {
                yield $plugin->registerCustomAssertions($customAssertionContext);
            });
        });

        return $injector;
    }
}