<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use Cspray\Labrador\AsyncUnit\Internal\InternalEventNames;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite;
use Psr\Log\NullLogger;

/**
 * @covers \Cspray\Labrador\AsyncUnit\TestFrameworkApplication
 */
class TestFrameworkApplicationTest extends \PHPUnit\Framework\TestCase {

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
        $state->passed = new \stdClass();
        $state->passed->events = [];
        $state->failed = new \stdClass();
        $state->failed->events = [];
        $this->emitter->on(Events::TEST_PASSED_EVENT, function($event) use($state) {
            $state->passed->events[] = $event;
        });
        $this->emitter->on(Events::TEST_FAILED_EVENT, function($event) use($state) {
            $state->failed->events[] = $event;
        });

        /** @var TestFrameworkApplication $application */
        return [$state, $this->injector->make(Application::class, [':testDirectories' => $dirs])];
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTest() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([dirname(__DIR__) . '/acme_src/SimpleTestCase/ImplicitDefaultTestSuite/SingleTest']);
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
            [$state, $application] = $this->getStateAndApplication([dirname(__DIR__) . '/acme_src/SimpleTestCase/ImplicitDefaultTestSuite/SingleTestAsyncAssertion']);
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
            [$state, $application] = $this->getStateAndApplication([dirname(__DIR__) . '/acme_src/SimpleTestCase/ImplicitDefaultTestSuite/NoAssertions']);
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
            $this->assertSame($msg, $testResult->getFailureException()->getMessage());
        });
    }

    public function testGettingFailureExceptionFromValidTestResultThrowsException() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([dirname(__DIR__) . '/acme_src/SimpleTestCase/ImplicitDefaultTestSuite/SingleTest']);
            yield $application->start();

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
            /** @var TestPassedEvent $event */
            $event = $state->passed->events[0];
            $this->assertInstanceOf(TestPassedEvent::class, $event);

            $testResult = $event->getTarget();

            $this->expectException(InvalidStateException::class);
            $this->expectExceptionMessage('Attempted to access a TestFailedException on a successful TestResult.');

            $testResult->getFailureException();
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteFailedAssertion() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([dirname(__DIR__) . '/acme_src/SimpleTestCase/ImplicitDefaultTestSuite/FailedAssertion']);
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

    public function testTestProcessingFinishedEventEmitted() {
        Loop::run(function() {
            [$state, $application] = $this->getStateAndApplication([dirname(__DIR__) . '/acme_src/SimpleTestCase/ImplicitDefaultTestSuite/SingleTest']);
            $this->emitter->on(InternalEventNames::TEST_INVOKED, function() use($state) {
                $state->data[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_PROCESSING_FINISHED_EVENT, function() use($state) {
                $state->data[] = 'test processing finished';
            });

            yield $application->start();

            $this->assertSame(['test invoked', 'test processing finished'], $state->data);
        });
    }

}