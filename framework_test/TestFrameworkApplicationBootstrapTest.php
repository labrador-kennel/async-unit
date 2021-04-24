<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\Application;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use Psr\Log\NullLogger;

/**
 * @covers \Cspray\Labrador\AsyncUnit\TestFrameworkApplicationBootstrap
 */
class TestFrameworkApplicationBootstrapTest extends \PHPUnit\Framework\TestCase {

    public function testCanCreateApplication() {
        $dirs = [dirname(__DIR__) . '/acme_src/ImplicitDefaultTestSuite/SingleTest'];
        $injector = (new TestFrameworkApplicationBootstrap(
            new StandardEnvironment(EnvironmentType::Test()),
            new NullLogger(),
            $dirs
        ))->getBootstrappedInjector();

        $application = $injector->make(Application::class);

        $this->assertInstanceOf(TestFrameworkApplication::class, $application);
    }

}