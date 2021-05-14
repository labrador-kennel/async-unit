<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Event\ProcessingStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestCaseFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestCaseStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestDisabledEvent;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Event\ProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteStartedEvent;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestDisabledException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestOutputException;
use Cspray\Labrador\AsyncUnit\Exception\TestSetupException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestTearDownException;
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use Cspray\Labrador\AsyncUnit\Parser\StaticAnalysisParser;
use Cspray\Labrador\AsyncUnit\Statistics\AggregateSummary;
use Cspray\Labrador\AsyncUnit\Statistics\PostRunSummary;
use Cspray\Labrador\AsyncUnit\Statistics\ProcessedAggregateSummary;
use Cspray\Labrador\AsyncUnit\Statistics\TestCaseSummary;
use Cspray\Labrador\AsyncUnit\Statistics\TestSuiteSummary;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionClass;
use stdClass;

class TestSuiteRunnerTest extends PHPUnitTestCase {

    use UsesAcmeSrc;

    private StaticAnalysisParser $parser;
    private EventEmitter $emitter;
    private CustomAssertionContext $customAssertionContext;
    private TestSuiteRunner $testSuiteRunner;

    public function setUp() : void {
        $this->parser = new StaticAnalysisParser();
        $this->emitter = new AmpEventEmitter();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->customAssertionContext = (new ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $this->testSuiteRunner = new TestSuiteRunner($this->emitter, $this->customAssertionContext, new NullRandomizer());
    }

    public function testImplicitDefaultTestSuiteSingleTestEmitsTestProcessedEventWithCorrectData() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('SingleTest'));
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);
            $this->assertInstanceOf(TestProcessedEvent::class, $state->events[0]);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $state->events[0]->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappens', $state->events[0]->getTarget()->getTestMethod());
            $this->assertTrue($state->events[0]->getTarget()->getTestCase()->getTestInvoked());
        });
    }

    public function testImplicitDefaultTestSuiteMultipleTestEmitsTestProcessedEventsWithCorrectData() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTest'));
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(3, $state->events);

            $ensureSomethingHappensMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingHappensMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class, $ensureSomethingHappensMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappens', $ensureSomethingHappensMethod->getTarget()->getTestMethod());
            $this->assertEquals([ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappens'], $ensureSomethingHappensMethod->getTarget()->getTestCase()->getInvokedMethods());
            
            $ensureSomethingHappensTwiceMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingHappensTwiceMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class, $ensureSomethingHappensTwiceMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappensTwice', $ensureSomethingHappensTwiceMethod->getTarget()->getTestMethod());
            $this->assertEquals([ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappensTwice'], $ensureSomethingHappensTwiceMethod->getTarget()->getTestCase()->getInvokedMethods());
        });
    }

    public function testImplicitDefaultTestSuiteHasSingleBeforeAllHookInvokedBeforeTest() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('HasSingleBeforeAllHook'));
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['beforeAll', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['beforeAll', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());
        });
    }

    public function testImplicitDefaultTestSuiteHasSingleBeforeEachHookInvokedBeforeTest() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('HasSingleBeforeEachHook'));
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['beforeEach', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['beforeEach', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());
        });
    }

    public function testImplicitDefaultTestSuiteHasSingleAfterAllHookInvokedAfterTest() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('HasSingleAfterAllHook'));
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['afterAll', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['afterAll', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());
        });
    }

    public function testImplicitDefaultTestSuiteHasSingleAfterEachHookInvokedAfterTest() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('HasSingleAfterEachHook'));
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['ensureSomething', 'afterEach'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['ensureSomethingTwice', 'afterEach'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());
        });
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingTestEmitsTestProcessedEventWithCorrectData() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingTest');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);
            /** @var TestProcessedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('throwsException', $testInvokedEvent->getTarget()->getTestMethod());
            $this->assertSame(TestState::Failed(), $testInvokedEvent->getTarget()->getState());

            $this->assertNotNull($testInvokedEvent->getTarget()->getException());
            $expectedMsg = 'An unexpected exception of type "Exception" with code 0 and message "Test failure" was thrown from #[Test] ' . ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class . '::throwsException';
            $this->assertSame($expectedMsg, $testInvokedEvent->getTarget()->getException()->getMessage());
            $this->assertSame(0, $testInvokedEvent->getTarget()->getException()->getCode());
            $this->assertInstanceOf(Exception::class, $testInvokedEvent->getTarget()->getException()->getPrevious());
            $this->assertSame('Test failure', $testInvokedEvent->getTarget()->getException()->getPrevious()->getMessage());
            $this->assertSame($dir . '/MyTestCase.php', $testInvokedEvent->getTarget()->getException()->getPrevious()->getFile());
        });
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingTestWithAfterEachHookInvokedAfterTest() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingTestWithAfterEachHook');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);
            /** @var TestProcessedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\ExceptionThrowingTestWithAfterEachHook\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('throwsException', $testInvokedEvent->getTarget()->getTestMethod());
            $this->assertTrue($testInvokedEvent->getTarget()->getTestCase()->getAfterHookCalled());
        });
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingBeforeAllHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingBeforeAll');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestCaseSetUpException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingBeforeAll\MyTestCase::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::beforeAll" #[BeforeAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the class beforeAll".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingAfterAllHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingAfterAll');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestCaseTearDownException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingAfterAll\MyTestCase::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::afterAll" #[AfterAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the class afterAll".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingBeforeEachHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingBeforeEach');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestSetUpException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingBeforeEach\MyTestCase::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::beforeEach" #[BeforeEach] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the object beforeEach".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingAfterEachHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingAfterEach');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestTearDownException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingAfterEach\MyTestCase::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::afterEach" #[AfterEach] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the object afterEach".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeAllHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeAll');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestSuiteSetUpException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeAll\MyTestSuite::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::throwException" #[BeforeAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in AttachToTestSuite".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeEachHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeEach');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestSuiteSetUpException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEach\MyTestSuite::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::throwEachException" #[BeforeEach] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite BeforeEach".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterEachHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterEach');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestSuiteTearDownException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterEach\MyTestSuite::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwEachException" #[AfterEach] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterEach".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterEachTestHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterEachTest');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestTearDownException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterEachTest\MyTestSuite::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwEachTestException" #[AfterEachTest] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterEachTest".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeEachTestHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeEachTest');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestSetUpException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEachTest\MyTestSuite::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::throwEachTestException" #[BeforeEachTest] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite BeforeEachTest".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterAllHaltsTestProcessing() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterAll');
            $results = yield $this->parser->parse($dir);

            $this->expectException(TestSuiteTearDownException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterAll\MyTestSuite::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwException" #[AfterAll] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterAll".');

            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testImplicitDefaultTestSuiteTestFailedExceptionThrowingTestEmitsTestProcessedEventWithCorrectData() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestFailedExceptionThrowingTest');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);
            /** @var TestProcessedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\TestFailedExceptionThrowingTest\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingFails', $testInvokedEvent->getTarget()->getTestMethod());
            $this->assertSame(TestState::Failed(), $testInvokedEvent->getTarget()->getState());

            $this->assertNotNull($testInvokedEvent->getTarget()->getException());
            $expectedMsg = 'Something barfed';
            $this->assertSame($expectedMsg, $testInvokedEvent->getTarget()->getException()->getMessage());
            $this->assertSame(0, $testInvokedEvent->getTarget()->getException()->getCode());
            $this->assertSame($dir . '/MyTestCase.php', $testInvokedEvent->getTarget()->getException()->getFile());
        });
    }

    public function testImplicitDefaultTestSuiteCustomAssertionsEmitsTestProcessedEventWithCorrectData() {
        Loop::run(function() {
            // Mock setup to make sure our custom assertion is being called properly
            $assertion = $this->getMockBuilder(Assertion::class)->getMock();
            $assertionResult = $this->getMockBuilder(AssertionResult::class)->getMock();
            $assertionResult->expects($this->once())->method('isSuccessful')->willReturn(true);
            $assertion->expects($this->once())->method('assert')->willReturn($assertionResult);

            $asyncAssertion = $this->getMockBuilder(AsyncAssertion::class)->getMock();
            $asyncAssertionResult = $this->getMockBuilder(AssertionResult::class)->getMock();
            $asyncAssertionResult->expects($this->once())->method('isSuccessful')->willReturn(true);
            $asyncAssertion->expects($this->once())->method('assert')->willReturn(new Success($asyncAssertionResult));

            $this->customAssertionContext->registerAssertion('theCustomAssertion', fn() => $assertion);
            $this->customAssertionContext->registerAsyncAssertion('theCustomAssertion', fn() => $asyncAssertion);

            // Normal TestSuiteRunner testing
            $dir = $this->implicitDefaultTestSuitePath('CustomAssertions');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });
            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);
            /** @var TestProcessedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\CustomAssertions\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('ensureCustomAssertionsPass', $testInvokedEvent->getTarget()->getTestMethod());

            $this->assertNull($testInvokedEvent->getTarget()->getException());
        });
    }

    public function testImplicitDefaultTestSuiteHasDataProviderEmitsTestProcessedEventsForEachDataSetOnUniqueTestCase() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('HasDataProvider');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(3, $state->events);

            /** @var TestProcessedEvent $firstEvent */
            $firstEvent = $state->events[0];
            $this->assertSame(1, $firstEvent->getTarget()->getTestCase()->getCounter());
        });
    }

    public function testExplicitTestSuiteDefaultExplicitTestSuite() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('AnnotatedDefaultTestSuite');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);
            $this->assertNull($state->events[0]->getTarget()->getException());
            $this->assertSame(ExplicitTestSuite\AnnotatedDefaultTestSuite\MyTestSuite::class, $state->events[0]->getTarget()->getTestCase()->getTestSuiteName());
        });
    }

    public function testImplicitDefaultTestSuiteMultipleBeforeAllHooksAllInvokedBeforeTest() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('MultipleBeforeAllHooks');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                 $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);
            $this->assertCount(2, $state->events);
            $allResults = array_map(
                fn(TestProcessedEvent $testInvokedEvent) => $testInvokedEvent->getTarget()->getTestCase()->getState(),
                $state->events
            );
            $this->assertEqualsCanonicalizing(
                [ImplicitDefaultTestSuite\MultipleBeforeAllHooks\FirstTestCase::class, ImplicitDefaultTestSuite\MultipleBeforeAllHooks\SecondTestCase::class],
                $allResults
            );
        });
    }

    public function testExplicitTestSuiteBeforeAllTestSuiteHookTestCaseHasAccessToSameTestSuite() : void {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('BeforeAllTestSuiteHook');
            $results = yield $this->parser->parse($dir);

            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                 $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);
            $this->assertCount(3, $state->events);

            $testCaseTestSuites = [];
            foreach ($state->events as $testInvokedEvent) {
                $testCaseTestSuites[] = $testInvokedEvent->getTarget()->getTestCase()->testSuite();
            }

            $testSuite = $state->events[0]->getTarget()->getTestCase()->testSuite();
            $this->assertSame([$testSuite, $testSuite, $testSuite], $testCaseTestSuites);
        });
    }

    public function testTestPassedEventsEmittedAfterTestProcessedEvent() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('SingleTest');
            $results = yield $this->parser->parse($dir);

            $state = new stdClass();
            $state->data = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use($state) {
                $state->data[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_PASSED, function() use($state) {
                $state->data[] = 'test passed';
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertSame(['test invoked', 'test passed'], $state->data);
        });
    }

    public function testTestFailedEventsEmittedAfterTestProcessedEvent() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('FailedAssertion');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->data = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use($state) {
                $state->data[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_FAILED, function() use($state) {
                $state->data[] = 'test failed';
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertSame(['test invoked', 'test failed'], $state->data);
        });
    }

    public function testTestSuiteStartedAndFinishedEventsEmittedInOrder() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('SingleTest');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $state->order = [];
            $this->emitter->on(Events::TEST_SUITE_STARTED, function($event) use($state) {
                $state->events[] = $event;
                $state->order[] = 'test suite start';
            });
            $this->emitter->on(Events::TEST_PROCESSED, function() use($state) {
                $state->order[] = 'test processed';
            });
            $this->emitter->on(Events::TEST_SUITE_FINISHED, function($event) use($state) {
                $state->events[] = $event;
                $state->order[] = 'test finished';
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);
            $this->assertInstanceOf(TestSuiteStartedEvent::class, $state->events[0]);
            $this->assertInstanceOf(TestSuiteFinishedEvent::class, $state->events[1]);
            $this->assertSame(['test suite start', 'test processed', 'test finished'], $state->order);
        });
    }

    public function testTestCaseProcessingEventEmitted() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('SingleTest');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_CASE_STARTED, function($event) use($state) {
                $state->events[] = $event;
            });
            $this->emitter->on(Events::TEST_CASE_FINISHED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);
            $this->assertInstanceOf(TestCaseStartedEvent::class, $state->events[0]);
            $this->assertInstanceOf(TestCaseFinishedEvent::class, $state->events[1]);
        });
    }

    public function testTestMethodIsNotInvokedWhenDisabled() : void {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestDisabled');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);
            $this->assertEqualsCanonicalizing(
                [TestState::Passed(), TestState::Disabled()],
                [$state->events[0]->getTarget()->getState(), $state->events[1]->getTarget()->getState()]
            );
        });
    }

    public function testTestMethodIsNotInvokedWhenTestCaseDisabled() : void {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestCaseDisabled');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(3, $state->events);
            $isDisabled = array_map(fn(TestProcessedEvent $event) => $event->getTarget()->getState(), $state->events);
            $this->assertSame([TestState::Disabled(), TestState::Disabled(), TestState::Disabled()], $isDisabled);
            $testCaseData = array_map(fn(TestProcessedEvent $event) => $event->getTarget()->getTestCase()->getData(), $state->events);
            $this->assertSame([[], [], []], $testCaseData);
        });
    }

    public function testTestResultWhenTestDisabled() : void {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestDisabled');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);
            $disabledTestResult = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class, 'skippedTest');

            $this->assertInstanceOf(ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class, $disabledTestResult->getTarget()->getTestCase());
            $this->assertSame('skippedTest', $disabledTestResult->getTarget()->getTestMethod());
            $this->assertSame(TestState::Disabled(), $disabledTestResult->getTarget()->getState());
            $this->assertInstanceOf(TestDisabledException::class, $disabledTestResult->getTarget()->getException());
            $expected = sprintf(
                '%s::%s has been marked disabled via annotation',
                ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class,
                'skippedTest'
            );
            $this->assertSame($expected, $disabledTestResult->getTarget()->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteHandleNonPhpFiles() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('HandleNonPhpFiles');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PASSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);
        });
    }

    public function testImplicitDefaultTestSuiteTestDisabledHookNotInvoked() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestDisabledHookNotInvoked');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);

            $disabledTestEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestDisabledHookNotInvoked\MyTestCase::class, 'disabledTest');

            $this->assertSame(TestState::Disabled(), $disabledTestEvent->getTarget()->getState());
            $this->assertSame([], $disabledTestEvent->getTarget()->getTestCase()->getState());

            $enabledTestEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestDisabledHookNotInvoked\MyTestCase::class, 'enabledTest');

            $this->assertSame(TestState::Passed(), $enabledTestEvent->getTarget()->getState());
            $this->assertSame(['before', 'enabled', 'after'], $enabledTestEvent->getTarget()->getTestCase()->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestCaseDisabledHookNotInvoked() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestCaseDisabledHookNotInvoked');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(2, $state->events);

            $testOneEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestCaseDisabledHookNotInvoked\MyTestCase::class, 'testOne');

            $this->assertSame(TestState::Disabled(), $testOneEvent->getTarget()->getState());
            $this->assertSame([], $testOneEvent->getTarget()->getTestCase()->getState());

            $testTwoEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestCaseDisabledHookNotInvoked\MyTestCase::class, 'testTwo');

            $this->assertSame(TestState::Disabled(), $testTwoEvent->getTarget()->getState());
            $this->assertSame([], $testTwoEvent->getTarget()->getTestCase()->getState());
        });
    }

    public function testExplicitTestSuiteTestSuiteDisabledHookNotInvoked() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('TestSuiteDisabledHookNotInvoked');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);

            $testSomethingEvent = $this->fetchTestProcessedEventForTest($state->events, ExplicitTestSuite\TestSuiteDisabledHookNotInvoked\MyTestCase::class, 'testSomething');

            $this->assertSame(TestState::Disabled(), $testSomethingEvent->getTarget()->getState());

            $this->assertSame([], $testSomethingEvent->getTarget()->getTestCase()->testSuite()->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestDisabledCustomMessage() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestDisabledCustomMessage');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);

            $testOneEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestDisabledCustomMessage\MyTestCase::class, 'testOne');

            $this->assertSame(TestState::Disabled(), $testOneEvent->getTarget()->getState());
            $this->assertInstanceOf(TestDisabledException::class, $testOneEvent->getTarget()->getException());
            $this->assertSame('Not sure what we should do here yet', $testOneEvent->getTarget()->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestCaseDisabledCustomMessage() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestCaseDisabledCustomMessage');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);

            $testOneEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestCaseDisabledCustomMessage\MyTestCase::class, 'testOne');

            $this->assertSame(TestState::Disabled(), $testOneEvent->getTarget()->getState());
            $this->assertInstanceOf(TestDisabledException::class, $testOneEvent->getTarget()->getException());
            $this->assertSame('The TestCase is disabled', $testOneEvent->getTarget()->getException()->getMessage());
        });
    }

    public function testExplicitTestSuiteTestSuiteDisabledCustomMessage() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('TestSuiteDisabledCustomMessage');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);

            $testOneEvent = $this->fetchTestProcessedEventForTest($state->events, ExplicitTestSuite\TestSuiteDisabledCustomMessage\MyTestCase::class, 'testOne');

            $this->assertSame(TestState::Disabled(), $testOneEvent->getTarget()->getState());
            $this->assertInstanceOf(TestDisabledException::class, $testOneEvent->getTarget()->getException());
            $this->assertSame('The AttachToTestSuite is disabled', $testOneEvent->getTarget()->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestDisabledEvents() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestDisabledEvents');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PASSED, fn($event) => $state->events[] = $event);
            $this->emitter->on(Events::TEST_FAILED, fn($event) => $state->events[] = $event);
            $this->emitter->on(Events::TEST_DISABLED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(3, $state->events);

            $failingEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testFailingFloatEquals');

            $this->assertInstanceOf(TestFailedEvent::class, $failingEvent);
            $this->assertSame(TestState::Failed(), $failingEvent->getTarget()->getState());

            $passingEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testIsTrue');

            $this->assertInstanceOf(TestPassedEvent::class, $passingEvent);
            $this->assertSame(TestState::Passed(), $passingEvent->getTarget()->getState());

            $disabledEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testIsDisabled');

            $this->assertInstanceOf(TestDisabledEvent::class, $disabledEvent);
            $this->assertSame(TestState::Disabled(), $disabledEvent->getTarget()->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestHasOutput() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestHasOutput');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_FAILED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->events);

            $failingEvent = $this->fetchTestProcessedEventForTest($state->events, ImplicitDefaultTestSuite\TestHasOutput\MyTestCase::class, 'testProducesOutput');

            $this->assertInstanceOf(TestFailedEvent::class, $failingEvent);
            $this->assertInstanceOf(TestOutputException::class, $failingEvent->getTarget()->getException());
            $this->assertSame("Test had unexpected output:\n\n\"testProducesOutput\"", $failingEvent->getTarget()->getException()->getMessage());
        });
    }

    public function testRandomizerIsUtilized() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('MultipleTest');
            $results = yield $this->parser->parse($dir);
            $testSuites = $results->getTestSuiteModels();
            $randomizer = $this->getMockBuilder(Randomizer::class)->getMock();

            $testSuiteRunner = new TestSuiteRunner(
                $this->emitter,
                $this->customAssertionContext,
                $randomizer
            );

            $this->assertCount(1, $testSuites);
            $this->assertNotEmpty($testSuites[0]->getTestCaseModels());
            $randomizer->expects($this->exactly(3))
                ->method('randomize')
                ->withConsecutive(
                    [$testSuites],
                    [$testSuites[0]->getTestCaseModels()],
                    [$testSuites[0]->getTestCaseModels()[0]->getTestMethodModels()]
                )
                ->willReturnOnConsecutiveCalls(
                    $testSuites,
                    $testSuites[0]->getTestCaseModels(),
                    $testSuites[0]->getTestCaseModels()[0]->getTestMethodModels()
                );

            yield $testSuiteRunner->runTestSuites($results);
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionOnly() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestExpectsExceptionOnly');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->passed = new stdClass();
            $state->passed->events = [];
            $state->failed = new stdClass();
            $state->failed->events = [];
            $state->disabled = new stdClass();
            $state->disabled->events = [];

            $this->emitter->on(Events::TEST_PASSED, fn($event) => $state->passed->events[] = $event);
            $this->emitter->on(Events::TEST_FAILED, fn($event) => $state->failed->events[] = $event);
            $this->emitter->on(Events::TEST_DISABLED, fn($event) => $state->disabled->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
            $this->assertCount(0, $state->disabled->events);
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionWrongType() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestExpectsExceptionWrongType');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->passed = new stdClass();
            $state->passed->events = [];
            $state->failed = new stdClass();
            $state->failed->events = [];
            $state->disabled = new stdClass();
            $state->disabled->events = [];

            $this->emitter->on(Events::TEST_PASSED, fn($event) => $state->passed->events[] = $event);
            $this->emitter->on(Events::TEST_FAILED, fn($event) => $state->failed->events[] = $event);
            $this->emitter->on(Events::TEST_DISABLED, fn($event) => $state->disabled->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(0, $state->passed->events);
            $this->assertCount(1, $state->failed->events);
            $this->assertCount(0, $state->disabled->events);

            /** @var TestFailedEvent $testFailedEvent */
            $testFailedEvent = $state->failed->events[0];

            $this->assertInstanceOf(TestFailedException::class, $testFailedEvent->getTarget()->getException());
            $expected = sprintf(
                'Failed asserting that thrown exception %s extends expected %s',
                InvalidStateException::class,
                InvalidArgumentException::class
            );
            $this->assertSame($expected, $testFailedEvent->getTarget()->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionMessage() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestExpectsExceptionMessage');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->passed = new stdClass();
            $state->passed->events = [];
            $state->failed = new stdClass();
            $state->failed->events = [];
            $state->disabled = new stdClass();
            $state->disabled->events = [];

            $this->emitter->on(Events::TEST_PASSED, fn($event) => $state->passed->events[] = $event);
            $this->emitter->on(Events::TEST_FAILED, fn($event) => $state->failed->events[] = $event);
            $this->emitter->on(Events::TEST_DISABLED, fn($event) => $state->disabled->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->passed->events);
            $this->assertCount(0, $state->failed->events);
            $this->assertCount(0, $state->disabled->events);
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionWrongMessage() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestExpectsExceptionWrongMessage');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->passed = new stdClass();
            $state->passed->events = [];
            $state->failed = new stdClass();
            $state->failed->events = [];
            $state->disabled = new stdClass();
            $state->disabled->events = [];

            $this->emitter->on(Events::TEST_PASSED, fn($event) => $state->passed->events[] = $event);
            $this->emitter->on(Events::TEST_FAILED, fn($event) => $state->failed->events[] = $event);
            $this->emitter->on(Events::TEST_DISABLED, fn($event) => $state->disabled->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(0, $state->passed->events);
            $this->assertCount(1, $state->failed->events);
            $this->assertCount(0, $state->disabled->events);
            $testFailedEvent = $state->failed->events[0];

            $this->assertInstanceOf(TestFailedException::class, $testFailedEvent->getTarget()->getException());
            $expected = sprintf(
                'Failed asserting that thrown exception message "%s" equals expected "%s"',
                'This is NOT the message that I expect',
                'This is the message that I expect'
            );
            $this->assertSame($expected, $testFailedEvent->getTarget()->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionDoesNotThrow() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestExpectsExceptionDoesNotThrow');
            $results = yield $this->parser->parse($dir);
            $state = new stdClass();
            $state->passed = new stdClass();
            $state->passed->events = [];
            $state->failed = new stdClass();
            $state->failed->events = [];
            $state->disabled = new stdClass();
            $state->disabled->events = [];

            $this->emitter->on(Events::TEST_PASSED, fn($event) => $state->passed->events[] = $event);
            $this->emitter->on(Events::TEST_FAILED, fn($event) => $state->failed->events[] = $event);
            $this->emitter->on(Events::TEST_DISABLED, fn($event) => $state->disabled->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(0, $state->passed->events);
            $this->assertCount(1, $state->failed->events);
            $this->assertCount(0, $state->disabled->events);
            $testFailedEvent = $state->failed->events[0];

            $this->assertInstanceOf(TestFailedException::class, $testFailedEvent->getTarget()->getException());
            $expected = sprintf(
                'Failed asserting that an exception of type %s is thrown',
                InvalidArgumentException::class
            );
            $this->assertSame($expected, $testFailedEvent->getTarget()->getException()->getMessage());
        });
    }

    public function testTestSuiteStartedEventHasTestSuiteSummary() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('KitchenSink'));
            $state = new stdClass();
            $state->data = [];
            $this->emitter->on(Events::TEST_SUITE_STARTED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(3, $state->data);

            $this->assertInstanceOf(TestSuiteSummary::class, $state->data[0]->getTarget());
            $this->assertInstanceOf(TestSuiteSummary::class, $state->data[1]->getTarget());
            $this->assertInstanceOf(TestSuiteSummary::class, $state->data[2]->getTarget());
        });
    }

    public function testTestCaseStartedEventHasTestCaseSummary() : void {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTest'));
            $state = new stdClass();
            $state->data = [];
            $this->emitter->on(Events::TEST_CASE_STARTED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            $this->assertInstanceOf(TestCaseSummary::class, $state->data[0]->getTarget());
        });
    }

    public function testTestProcessingEventsEmittedInOrder() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('SingleTest'));
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::TEST_PROCESSED, function() use($state) {
                $state->data[] = 'test invoked';
            });
            $this->emitter->on(Events::PROCESSING_FINISHED, function() use($state) {
                $state->data[] = 'test processing finished';
            });
            $this->emitter->on(Events::PROCESSING_STARTED, function() use($state) {
                $state->data[] = 'test processing started';
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertSame(['test processing started', 'test invoked', 'test processing finished'], $state->data);
        });
    }

    public function testTestProcessingStartedHasAggregateSummary() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('TestCaseDisabled'));
            $state = new stdClass();
            $state->data = [];
            $this->emitter->on(Events::PROCESSING_STARTED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingStartedEvent $testStartedEvent */
            $testStartedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingStartedEvent::class, $testStartedEvent);
            $this->assertInstanceOf(AggregateSummary::class, $testStartedEvent->getTarget());
        });
    }

    public function processedAggregateSummaryTestSuiteInfoProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitTestSuite::class, ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class
            ]]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryTestSuiteInfoProvider
     */
    public function testTestProcessingFinishedHasProcessedAggregateSummaryWithCorrectTestSuiteNames(string $path, array $expected) {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);

            $summary = $testFinishedEvent->getTarget();

            $this->assertEqualsCanonicalizing(
                $expected,
                $summary->getTestSuiteNames()
            );
        });
    }

    public function processedAggregateSummaryWithCorrectTotalTestSuiteCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 3]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectTotalTestSuiteCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectTotalTestSuiteCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getTotalTestSuiteCount());
        });
    }


    public function processedAggregateSummaryWithCorrectDisabledTestSuiteCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 0],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 1]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectDisabledTestSuiteCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectDisabledTestSuiteCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getDisabledTestSuiteCount());
        });
    }

    public function processedAggregateSummaryWithCorrectEnabledTestSuiteCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 3],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 0]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectEnabledTestSuiteCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectEnabledTestSuiteCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getEnabledTestSuiteCount());
        });
    }

    public function processedAggregateSummaryWithCorrectTotalTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 6],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 2]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectTotalTestCaseCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectTotalTestCaseCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getTotalTestCaseCount());
        });
    }

    public function processedAggregateSummaryWithCorrectDisabledTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 0],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 2],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 1]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectDisabledTestCaseCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectDisabledTestCaseCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getDisabledTestCaseCount());
        });
    }

    public function processedAggregateSummaryWithCorrectEnabledTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 6],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 0]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectEnabledTestCaseCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectEnabledTestCaseCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getEnabledTestCaseCount());
        });
    }

    public function processedAggregateSummaryWithCorrectTotalTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 12],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 3],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 3]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectTotalTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectTotalTestCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getTotalTestCount());
        });
    }

    public function processedAggregateSummaryWithCorrectDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 3],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 3],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 3]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectDisabledTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectDisabledTestCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getDisabledTestCount());
        });
    }

    public function processedAggregateSummaryWithCorrectEnabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 9],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 0]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectEnabledTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectEnabledTestCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getEnabledTestCount());
        });
    }

    public function processedAggregateSummaryWithCorrectPassedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 8],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 0]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectPassedTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectPassedTestCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getPassedTestCount());
        });
    }

    public function processedAggregateSummaryWithCorrectFailedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 1],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), 1]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectFailedTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectFailedTestCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getFailedTestCount());
        });
    }

    public function processedAggregateSummaryWithCorrectAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('MultipleTest'), 3],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 4],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), 18]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectAssertionCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectAssertionCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getAssertionCount());
        });
    }

    public function processedAggregateSummaryWithCorrectAsyncAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('MultipleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 6],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), 4]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectAsyncAssertionCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectAsyncAssertionCount(string $path, int $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->data[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $state->data);
            /** @var ProcessingFinishedEvent $testFinishedEvent */
            $testFinishedEvent = $state->data[0];

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
            $this->assertSame($expected, $testFinishedEvent->getTarget()->getAsyncAssertionCount());
        });
    }

    public function testProcessedAggregateSummaryHasDuration() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
            $state = new stdClass();
            $state->event = null;

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->event = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $state->event);
            $this->assertGreaterThan(600, $state->event->getTarget()->getDuration()->asMilliseconds());
        });
    }

    public function testProcessedAggregateSummaryHasMemoryUsageInBytes() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('SingleTest'));
            $state = new stdClass();
            $state->event = null;

            $this->emitter->on(Events::PROCESSING_FINISHED, function($event) use($state) {
                $state->event = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertInstanceOf(ProcessingFinishedEvent::class, $state->event);
            $this->assertGreaterThan(1000, $state->event->getTarget()->getMemoryUsageInBytes());
        });
    }

    private function fetchTestProcessedEventForTest(array $events, string $testClass, string $method) : Event {
        foreach ($events as $event) {
            if ($event->getTarget()->getTestCase()::class === $testClass && $event->getTarget()->getTestMethod() === $method) {
                return $event;
            }
        }
        $this->fail('Expected events to have a TestCase and method matching ' . $testClass . '::' . $method);
    }
}