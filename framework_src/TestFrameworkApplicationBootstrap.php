<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Auryn\Injector;
use Cspray\Labrador\AsyncUnit\Internal\Parser;
use Cspray\Labrador\Environment;
use Psr\Log\LoggerInterface;

final class TestFrameworkApplicationBootstrap {

    private Environment $environment;
    private LoggerInterface $logger;
    private array $scanDirs;

    public function __construct(Environment $environment, LoggerInterface $logger, array $scanDirs) {
        $this->environment = $environment;
        $this->logger = $logger;
        $this->scanDirs = $scanDirs;
    }

    public function getBootstrappedInjector() : Injector {
        $injector = (new TestFrameworkApplicationObjectGraph($this->environment, $this->logger))->wireObjectGraph();

        $parser = new Parser();
        $results = $parser->parse($this->scanDirs);

        $injector->share($results);

        return $injector;
    }

}