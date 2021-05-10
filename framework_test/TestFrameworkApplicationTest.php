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
    }

    private function getStateAndApplication(array $dirs) : Promise {
        return call(function() use($dirs) {
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

            $parserResult = yield $this->injector->make(Parser::class)->parse($dirs);

            /** @var TestFrameworkApplication $application */
            return [$state, $this->injector->make(Application::class, [':parserResult' => $parserResult])];

        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTest() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);
            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
            /** @var TestPassedEvent $event */
            $event = $state->passed->events[0];
            $this->assertInstanceOf(TestPassedEvent::class, $event);

            $testResult = $event->getTarget();

            $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $testResult->getTestCase());
            $this->assertSame('ensureSomethingHappens', $testResult->getTestMethod());
            $this->assertSame(TestState::Passed(), $testResult->getState());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTestAsyncAssertion() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTestAsyncAssertion')]);
            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
            /** @var TestPassedEvent $event */
            $event = $state->passed->events[0];
            $this->assertInstanceOf(TestPassedEvent::class, $event);

            $testResult = $event->getTarget();

            $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTestAsyncAssertion\MyTestCase::class, $testResult->getTestCase());
            $this->assertSame('ensureAsyncAssert', $testResult->getTestMethod());
            $this->assertSame(TestState::Passed(), $testResult->getState());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteNoAssertions() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('NoAssertions')]);
            yield $application->start();

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
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteFailedAssertion() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('FailedAssertion')]);
            yield $application->start();

            $this->assertCount(0, $state->passed->events);
            $this->assertCount(1, $state->failed->events);
            /** @var TestFailedEvent $event */
            $event = $state->failed->events[0];
            $this->assertInstanceOf(TestFailedEvent::class, $event);

            $testResult = $event->getTarget();
            $this->assertSame(TestState::Failed(), $testResult->getState());
        });
    }

    public function testTestProcessingEventsEmitted() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);
            $this->emitter->on(Events::TEST_PROCESSED, function() use($state) {
                $state->data[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function() use($state) {
                $state->data[] = 'test processing finished';
            });
            $this->emitter->on(Events::TEST_PROCESSING_STARTED, function() use($state) {
                $state->data[] = 'test processing started';
            });

            yield $application->start();

            $this->assertSame(['test processing started', 'test invoked', 'test processing finished'], $state->data);
        });
    }

    public function testTestProcessingStartedHasPreRunSummary() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('ExtendedTestCases')]);
            $this->emitter->on(Events::TEST_PROCESSING_STARTED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $application->start();

            $this->assertCount(1, $state->data);
            /** @var TestProcessingStartedEvent $testStartedEvent */
            $testStartedEvent = $state->data[0];

            $this->assertInstanceOf(TestProcessingStartedEvent::class, $testStartedEvent);
            $this->assertEquals(1, $testStartedEvent->getTarget()->getTestSuiteCount());
            $this->assertEquals(3, $testStartedEvent->getTarget()->getTotalTestCaseCount());
        });
    }

    public function testTestProcessingFinishedHasPostRunSummary() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('ExtendedTestCases')]);
            $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $application->start();

            $this->assertCount(1, $state->data);
            /** @var TestProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(TestProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame(9, $testFinishedEvent->getTarget()->getTotalTestCount());
            $this->assertSame(1, $testFinishedEvent->getTarget()->getFailedTestCount());
            $this->assertSame(8, $testFinishedEvent->getTarget()->getPassedTestCount());
            $this->assertSame(18, $testFinishedEvent->getTarget()->getAssertionCount());
            $this->assertSame(4, $testFinishedEvent->getTarget()->getAsyncAssertionCount());
        });
    }

    public function testLoadingCustomAssertionPlugins() {
        Loop::run(function() {
            /** @var Application $application */
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);

            $this->injector->share(FooAssertionPlugin::class);
            $this->injector->share(BarAssertionPlugin::class);

            $application->registerPlugin(FooAssertionPlugin::class);
            $application->registerPlugin(BarAssertionPlugin::class);

            yield $application->loadPlugins();

            $actual = $this->injector->make(CustomAssertionContext::class);

            $fooPlugin = $this->injector->make(FooAssertionPlugin::class);
            $barPlugin = $this->injector->make(BarAssertionPlugin::class);

            $this->assertSame($fooPlugin->getCustomAssertionContext(), $actual);
            $this->assertSame($barPlugin->getCustomAssertionContext(), $actual);
        });
    }

    public function testExplicitTestSuiteTestSuiteStateShared() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->explicitTestSuitePath('TestSuiteStateBeforeAll')]);

            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
        });
    }

    public function testExplicitTestSuiteTestCaseBeforeAllHasTestSuiteState() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->explicitTestSuitePath('TestCaseBeforeAllHasTestSuiteState')]);

            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
        });
    }

    public function testExplicitTestSuiteTestCaseAfterAllHasTestSuiteState() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->explicitTestSuitePath('TestCaseAfterAllHasTestSuiteState')]);

            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);

            $this->assertSame('AsyncUnit', $state->passed->events[0]->getTarget()->getTestCase()->getState());
        });
    }

    public function testExplicitTestSuiteTestSuiteDisabledPostRunSummary() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->explicitTestSuitePath('TestSuiteDisabled')]);

            $postRunSummary = null;
            $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function(TestProcessingFinishedEvent $event) use(&$postRunSummary) {
                $postRunSummary = $event->getTarget();
            });

            yield $application->start();
            $this->assertCount(3, $state->disabled->events);
            $this->assertCount(0, $state->passed->events);
            $this->assertCount(0, $state->failed->events);

            $this->assertInstanceOf(PostRunSummary::class, $postRunSummary);

            $this->assertSame(3, $postRunSummary->getTotalTestCount());
            $this->assertSame(0, $postRunSummary->getPassedTestCount());
            $this->assertSame(0, $postRunSummary->getFailedTestCount());
            $this->assertSame(3, $postRunSummary->getDisabledTestCount());
        });
    }

    public function testImplicitDefaultTestSuiteTestKnownTime() {
        Loop::run(function() {
            [$state, $application] = yield $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('TestKnownRunTime')]);

            $postRunSummary = null;
            $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function(TestProcessingFinishedEvent $event) use(&$postRunSummary) {
                $postRunSummary = $event->getTarget();
            });

            yield $application->start();

            $this->assertInstanceOf(PostRunSummary::class, $postRunSummary);
            $this->assertSame(1, $postRunSummary->getTotalTestCount());
            $this->assertSame(1, $postRunSummary->getPassedTestCount());
            $this->assertGreaterThan(0.500, $postRunSummary->getDuration()->asMicroseconds());
            // just making an assumption here that we should be using more than 1000 bytes? not sure how to test this
            // without introducing an interface to abstract getting memory usage... not something we want to do at this point
            $this->assertGreaterThan(1000, $postRunSummary->getMemoryUsageInBytes());
        });
    }

}