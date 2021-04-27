<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Internal\Parser;
use Cspray\Labrador\AsyncUnit\Internal\TestSuiteRunner;
use Cspray\Labrador\CoreApplicationObjectGraph;
use Cspray\Labrador\Environment;
use Cspray\Labrador\SettingsLoader;
use Psr\Log\LoggerInterface;

final class TestFrameworkApplicationObjectGraph extends CoreApplicationObjectGraph {

    public function wireObjectGraph() : Injector {
        $injector = parent::wireObjectGraph();

        $customAssertionContext = (new \ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $injector->share($customAssertionContext);

        $injector->share(Parser::class);
        $injector->share(TestSuiteRunner::class);

        $injector->share(Application::class);
        $injector->alias(Application::class, TestFrameworkApplication::class);
        $injector->prepare(Application::class, function(Application $application) use($customAssertionContext) {
            $application->registerPluginLoadHandler(CustomAssertionPlugin::class, function(CustomAssertionPlugin $plugin) use($customAssertionContext) {
                yield $plugin->registerCustomAssertions($customAssertionContext);
            });
        });

        return $injector;
    }
}