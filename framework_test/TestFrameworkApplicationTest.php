<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Amp\Promise;
use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessingStartedEvent;
use Cspray\Labrador\AsyncUnit\Stub\BarAssertionPlugin;
use Cspray\Labrador\AsyncUnit\Stub\FooAssertionPlugin;
use Cspray\Labrador\AsyncUnitCli\DefaultResultPrinter;
use Cspray\Labrador\Engine;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Psr\Log\NullLogger;
use function Amp\call;
use function Amp\Promise\wait;

class TestFrameworkApplicationTest extends \PHPUnit\Framework\TestCase {

    use UsesAcmeSrc;

    private Injector $injector;

    private EventEmitter $emitter;

    public function setUp() : void {
        $environment = new StandardEnvironment(EnvironmentType::Test());
        $logger = new NullLogger();
        $objectGraph = (new TestFrameworkApplicationObjectGraph($environment, $logger))->wireObjectGraph();

        $this->emitter = $objectGraph->make(EventEmitter::class);
        $this->injector = $objectGraph;
        $this->injector->define(DefaultResultPrinter::class, [':version' => 'test']);
    }

    private function getStateAndApplication(array $dirs) : array {
        $state = new \stdClass();
        $state->data = [];
        $state->passed = new \stdClass();
        $state->passed->events = [];
        $state->failed = new \stdClass();
        $state->failed->events = [];
        $state->disabled = new \stdClass();
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

    public function testTestProcessingEventsEmitted() {
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);

        $this->emitter->on(Events::TEST_PROCESSED, function() use($state) {
            $state->data[] = 'test invoked';
        });
        $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function() use($state) {
            $state->data[] = 'test processing finished';
        });
        $this->emitter->on(Events::TEST_PROCESSING_STARTED, function() use($state) {
            $state->data[] = 'test processing started';
        });

        $engine->run($this->injector->make(Application::class));
        $this->assertSame(['test processing started', 'test invoked', 'test processing finished'], $state->data);
    }

    public function testTestProcessingStartedHasPreRunSummary() {
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('ExtendedTestCases')]);
        $this->emitter->on(Events::TEST_PROCESSING_STARTED, function($event) use($state) {
            $state->data[] = $event;
        });

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->data);
        /** @var TestProcessingStartedEvent $testStartedEvent */
        $testStartedEvent = $state->data[0];

        $this->assertInstanceOf(TestProcessingStartedEvent::class, $testStartedEvent);
        $this->assertEquals(1, $testStartedEvent->getTarget()->getTestSuiteCount());
        $this->assertEquals(3, $testStartedEvent->getTarget()->getTotalTestCaseCount());
    }

    public function testTestProcessingFinishedHasPostRunSummary() {
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('ExtendedTestCases')]);
        $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function($event) use($state) {
            $state->data[] = $event;
        });

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->data);
        /** @var TestProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $state->data[0];

        $this->assertInstanceOf(TestProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame(9, $testFinishedEvent->getTarget()->getTotalTestCount());
        $this->assertSame(1, $testFinishedEvent->getTarget()->getFailedTestCount());
        $this->assertSame(8, $testFinishedEvent->getTarget()->getPassedTestCount());
        $this->assertSame(18, $testFinishedEvent->getTarget()->getAssertionCount());
        $this->assertSame(4, $testFinishedEvent->getTarget()->getAsyncAssertionCount());
    }

    public function testLoadingCustomAssertionPlugins() {
        /** @var Application $application */
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);

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

    public function testExplicitTestSuiteTestSuiteDisabledPostRunSummary() {
        [$state, $engine] = $this->getStateAndApplication([$this->explicitTestSuitePath('TestSuiteDisabled')]);

        $postRunSummary = null;
        $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function(TestProcessingFinishedEvent $event) use(&$postRunSummary) {
            $postRunSummary = $event->getTarget();
        });

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(3, $state->disabled->events);
        $this->assertCount(0, $state->passed->events);
        $this->assertCount(0, $state->failed->events);

        $this->assertInstanceOf(PostRunSummary::class, $postRunSummary);

        $this->assertSame(3, $postRunSummary->getTotalTestCount());
        $this->assertSame(0, $postRunSummary->getPassedTestCount());
        $this->assertSame(0, $postRunSummary->getFailedTestCount());
        $this->assertSame(3, $postRunSummary->getDisabledTestCount());
    }

    public function testImplicitDefaultTestSuiteTestKnownTime() {
        [$state, $engine] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('TestKnownRunTime')]);

        $postRunSummary = null;
        $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function(TestProcessingFinishedEvent $event) use(&$postRunSummary) {
            $postRunSummary = $event->getTarget();
        });

        $engine->run($this->injector->make(Application::class));

        $this->assertInstanceOf(PostRunSummary::class, $postRunSummary);
        $this->assertSame(1, $postRunSummary->getTotalTestCount());
        $this->assertSame(1, $postRunSummary->getPassedTestCount());
        $this->assertGreaterThan(0.500, $postRunSummary->getDuration()->asMicroseconds());
        // just making an assumption here that we should be using more than 1000 bytes? not sure how to test this
        // without introducing an interface to abstract getting memory usage... not something we want to do at this point
        $this->assertGreaterThan(1000, $postRunSummary->getMemoryUsageInBytes());
    }

}