<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Event\ProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\ProcessingStartedEvent;
use Cspray\Labrador\AsyncUnit\Statistics\PostRunSummary;
use Cspray\Labrador\AsyncUnit\Statistics\AggregateSummary;
use Cspray\Labrador\AsyncUnit\Statistics\ProcessedAggregateSummary;
use Cspray\Labrador\AsyncUnit\Stub\BarAssertionPlugin;
use Cspray\Labrador\AsyncUnit\Stub\FooAssertionPlugin;
use Cspray\Labrador\AsyncUnitCli\DefaultResultPrinter;
use Cspray\Labrador\Engine;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Psr\Log\NullLogger;
use stdClass;

class TestFrameworkApplicationTest extends \PHPUnit\Framework\TestCase {

    use UsesAcmeSrc;

    private Injector $injector;

    private EventEmitter $emitter;

    public function setUp() : void {
        $environment = new StandardEnvironment(EnvironmentType::Test());
        $logger = new NullLogger();
        $objectGraph = (new TestFrameworkApplicationObjectGraph($environment, $logger))->wireObjectGraph();
        $objectGraph->alias(Randomizer::class, NullRandomizer::class);

        $this->emitter = $objectGraph->make(EventEmitter::class);
        $this->injector = $objectGraph;
        $this->injector->define(DefaultResultPrinter::class, [':version' => 'test']);
    }

    private function getStateAndApplication(array $dirs) : array {
        $state = new stdClass();
        $state->data = [];
        $state->passed = new stdClass();
        $state->passed->events = [];
        $state->failed = new stdClass();
        $state->failed->events = [];
        $state->disabled = new stdClass();
        $state->disabled->events = [];
        $this->emitter->on(Events::TEST_PASSED, function($event) use($state) {
            $state->passed->events[] = $event;
        });
        $this->emitter->on(Events::TEST_FAILED, function($event) use($state) {
            $state->failed->events[] = $event;
        });
        $this->emitter->on(Events::TEST_DISABLED, function($event) use($state) {
            $state->disabled->events[] = $event;
        });
        $this->injector->define(Application::class, [':dirs' => $dirs]);
        /** @var TestFrameworkApplication $application */
        return [$state, $this->injector->make(Engine::class)];
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTest() {
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);
        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);
        /** @var TestPassedEvent $event */
        $event = $state->passed->events[0];
        $this->assertInstanceOf(TestPassedEvent::class, $event);

        $testResult = $event->getTarget();

        $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $testResult->getTestCase());
        $this->assertSame('ensureSomethingHappens', $testResult->getTestMethod());
        $this->assertSame(TestState::Passed(), $testResult->getState());
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTestAsyncAssertion() {
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTestAsyncAssertion')]);
        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);
        /** @var TestPassedEvent $event */
        $event = $state->passed->events[0];
        $this->assertInstanceOf(TestPassedEvent::class, $event);

        $testResult = $event->getTarget();

        $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTestAsyncAssertion\MyTestCase::class, $testResult->getTestCase());
        $this->assertSame('ensureAsyncAssert', $testResult->getTestMethod());
        $this->assertSame(TestState::Passed(), $testResult->getState());
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteNoAssertions() {
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('NoAssertions')]);
        $engine->run($this->injector->make(Application::class));

        $this->assertCount(0, $state->passed->events);
        $this->assertCount(1, $state->failed->events);
        /** @var TestFailedEvent $event */
        $event = $state->failed->events[0];
        $this->assertInstanceOf(TestFailedEvent::class, $event);

        $testResult = $event->getTarget();

        $this->assertInstanceOf(ImplicitDefaultTestSuite\NoAssertions\MyTestCase::class, $testResult->getTestCase());
        $this->assertSame('noAssertions', $testResult->getTestMethod());
        $this->assertSame(TestState::Failed(), $testResult->getState());
        $msg = sprintf(
            'Expected "%s::%s" #[Test] to make at least 1 Assertion but none were made.',
            ImplicitDefaultTestSuite\NoAssertions\MyTestCase::class,
            'noAssertions'
        );
        $this->assertSame($msg, $testResult->getException()->getMessage());
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteFailedAssertion() {
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('FailedAssertion')]);
        $engine->run($this->injector->make(Application::class));

        $this->assertCount(0, $state->passed->events);
        $this->assertCount(1, $state->failed->events);
        /** @var TestFailedEvent $event */
        $event = $state->failed->events[0];
        $this->assertInstanceOf(TestFailedEvent::class, $event);

        $testResult = $event->getTarget();
        $this->assertSame(TestState::Failed(), $testResult->getState());
    }

    public function testLoadingCustomAssertionPlugins() {
        /** @var Application $application */
        [,$engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);

        $this->injector->share(FooAssertionPlugin::class);
        $this->injector->share(BarAssertionPlugin::class);

        $application = $this->injector->make(Application::class);

        $application->registerPlugin(FooAssertionPlugin::class);
        $application->registerPlugin(BarAssertionPlugin::class);

        $engine->run($application);

        $actual = $this->injector->make(CustomAssertionContext::class);

        $fooPlugin = $this->injector->make(FooAssertionPlugin::class);
        $barPlugin = $this->injector->make(BarAssertionPlugin::class);

        $this->assertSame($fooPlugin->getCustomAssertionContext(), $actual);
        $this->assertSame($barPlugin->getCustomAssertionContext(), $actual);
    }

    public function testExplicitTestSuiteTestSuiteStateShared() {
        [$state, $engine] = $this->getStateAndApplication([$this->explicitTestSuitePath('TestSuiteStateBeforeAll')]);

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);
    }

    public function testExplicitTestSuiteTestCaseBeforeAllHasTestSuiteState() {
        [$state, $engine] = $this->getStateAndApplication([$this->explicitTestSuitePath('TestCaseBeforeAllHasTestSuiteState')]);

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);
    }

    public function testExplicitTestSuiteTestCaseAfterAllHasTestSuiteState() {
        [$state, $engine] = $this->getStateAndApplication([$this->explicitTestSuitePath('TestCaseAfterAllHasTestSuiteState')]);

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);

        $this->assertSame('AsyncUnit', $state->passed->events[0]->getTarget()->getTestCase()->getState());
    }


}