<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin\MyCustomAssertionPlugin;
use Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin\MyOtherCustomAssertionPlugin;
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

    public function testApplicationHasRegisteredPlugins() {
        $dirs = [dirname(__DIR__) . '/acme_src/ImplicitDefaultTestSuite/HasAssertionPlugin'];
        $injector = (new TestFrameworkApplicationBootstrap(
            new StandardEnvironment(EnvironmentType::Test()),
            new NullLogger(),
            $dirs
        ))->getBootstrappedInjector();

        $application = $injector->make(Application::class);

        $expected = [MyCustomAssertionPlugin::class, MyOtherCustomAssertionPlugin::class];

        $this->assertEqualsCanonicalizing($expected, $application->getRegisteredPlugins());
    }

}