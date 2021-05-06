<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
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

    private function getStateAndApplication(array $dirs) {
        $state = new \stdClass();
        $state->data = [];
        $state->passed = new \stdClass();
        $state->passed->events = [];
        $state->failed = new \stdClass();
        $state->failed->events = [];
        $this->emitter->on(Events::TEST_PASSED, function($event) use($state) {
            $state->passed->events[] = $event;
        });
        $this->emitter->on(Events::TEST_FAILED, function($event) use($state) {
            $state->failed->events[] = $event;
        });

        $parserResult = $this->injector->make(Parser::class)->parse($dirs);

        /** @var TestFrameworkApplication $application */
        return [$state, $this->injector->make(Application::class, [':parserResult' => $parserResult])];
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTest() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);
            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
            /** @var TestPassedEvent $event */
            $event = $state->passed->events[0];
            $this->assertInstanceOf(TestPassedEvent::class, $event);

            $testResult = $event->getTarget();

            $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $testResult->getTestCase());
            $this->assertSame('ensureSomethingHappens', $testResult->getTestMethod());
            $this->assertTrue($testResult->isSuccessful());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTestAsyncAssertion() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTestAsyncAssertion')]);
            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
            /** @var TestPassedEvent $event */
            $event = $state->passed->events[0];
            $this->assertInstanceOf(TestPassedEvent::class, $event);

            $testResult = $event->getTarget();

            $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTestAsyncAssertion\MyTestCase::class, $testResult->getTestCase());
            $this->assertSame('ensureAsyncAssert', $testResult->getTestMethod());
            $this->assertTrue($testResult->isSuccessful());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteNoAssertions() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('NoAssertions')]);
            yield $application->start();

            $this->assertCount(0, $state->passed->events);
            $this->assertCount(1, $state->failed->events);
            /** @var TestFailedEvent $event */
            $event = $state->failed->events[0];
            $this->assertInstanceOf(TestFailedEvent::class, $event);

            $testResult = $event->getTarget();

            $this->assertInstanceOf(ImplicitDefaultTestSuite\NoAssertions\MyTestCase::class, $testResult->getTestCase());
            $this->assertSame('noAssertions', $testResult->getTestMethod());
            $this->assertFalse($testResult->isSuccessful());
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
            [$state, $application] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('FailedAssertion')]);
            yield $application->start();

            $this->assertCount(0, $state->passed->events);
            $this->assertCount(1, $state->failed->events);
            /** @var TestFailedEvent $event */
            $event = $state->failed->events[0];
            $this->assertInstanceOf(TestFailedEvent::class, $event);

            $testResult = $event->getTarget();

            $this->assertFalse($testResult->isSuccessful());
        });
    }

    public function testTestProcessingEventsEmitted() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);
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
            [$state, $application] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('ExtendedTestCases')]);
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
            [$state, $application] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('ExtendedTestCases')]);
            $this->emitter->on(Events::TEST_PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $application->start();

            $this->assertCount(1, $state->data);
            /** @var TestProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(TestProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame(9, $testFinishedEvent->getTarget()->getExecutedTestCount());
            $this->assertSame(1, $testFinishedEvent->getTarget()->getFailureTestCount());
            $this->assertSame(18, $testFinishedEvent->getTarget()->getAssertionCount());
            $this->assertSame(4, $testFinishedEvent->getTarget()->getAsyncAssertionCount());
        });
    }

    public function testLoadingCustomAssertionPlugins() {
        Loop::run(function() {
            /** @var Application $application */
            [$state, $application] = $this->getStateAndApplication([$this->implicitDefaultTestSuitePath('SingleTest')]);

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
            [$state, $application] = $this->getStateAndApplication([$this->explicitTestSuitePath('TestSuiteStateBeforeAll')]);

            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
        });
    }

    public function testExplicitTestSuiteTestCaseBeforeAllHasTestSuiteState() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([$this->explicitTestSuitePath('TestCaseBeforeAllHasTestSuiteState')]);

            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
        });
    }

    public function testExplicitTestSuiteTestCaseAfterAllHasTestSuiteState() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([$this->explicitTestSuitePath('TestCaseAfterAllHasTestSuiteState')]);

            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);

            $this->assertSame('AsyncUnit', $state->passed->events[0]->getTarget()->getTestCase()->getState());
        });
    }

}