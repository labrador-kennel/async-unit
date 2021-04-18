<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\CoreApplicationObjectGraph;
use Cspray\Labrador\Environment;
use Cspray\Labrador\SettingsLoader;
use Psr\Log\LoggerInterface;

class TestFrameworkApplicationObjectGraph extends CoreApplicationObjectGraph {

    public function wireObjectGraph() : Injector {
        $injector = parent::wireObjectGraph();

        $injector->alias(Application::class, TestFrameworkApplication::class);

        return $injector;
    }
}