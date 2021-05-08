<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Event\TestCaseFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestCaseStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestDisabledEvent;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteStartedEvent;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestDisabledException;
use Cspray\Labrador\AsyncUnit\Exception\TestSetupException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestTearDownException;
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionClass;
use stdClass;

class TestSuiteRunnerTest extends PHPUnitTestCase {

    // The TestSuiteRunner assumes some other thing controlling it has started the loop

    use UsesAcmeSrc;

    private Parser $parser;
    private EventEmitter $emitter;
    private CustomAssertionContext $customAssertionContext;
    private TestSuiteRunner $testSuiteRunner;

    public function setUp() : void {
        $this->parser = new Parser();
        $this->emitter = new AmpEventEmitter();
        $this->customAssertionContext = (new ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $this->testSuiteRunner = new TestSuiteRunner($this->emitter, $this->customAssertionContext);
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTestInvokesMethod() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->implicitDefaultTestSuitePath('SingleTest'))->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            $this->assertInstanceOf(TestProcessedEvent::class, $state->events[0]);
            $this->assertInstanceOf(TestResult::class, $state->events[0]->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $state->events[0]->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappens', $state->events[0]->getTarget()->getTestMethod());
            $this->assertTrue($state->events[0]->getTarget()->getTestCase()->getTestInvoked());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteMultipleTestInvokesMethod() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTest'))->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(3, $state->events);

            $ensureSomethingHappensMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingHappensMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingHappensMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class, $ensureSomethingHappensMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappens', $ensureSomethingHappensMethod->getTarget()->getTestMethod());
            $this->assertEquals([ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappens'], $ensureSomethingHappensMethod->getTarget()->getTestCase()->getInvokedMethods());
            
            $ensureSomethingHappensTwiceMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingHappensTwiceMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingHappensTwiceMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class, $ensureSomethingHappensTwiceMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappensTwice', $ensureSomethingHappensTwiceMethod->getTarget()->getTestMethod());
            $this->assertEquals([ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappensTwice'], $ensureSomethingHappensTwiceMethod->getTarget()->getTestCase()->getInvokedMethods());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteHasSingleBeforeAllHook() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->implicitDefaultTestSuitePath('HasSingleBeforeAllHook'))->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['beforeAll', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['beforeAll', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteHasSingleBeforeEachHook() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->implicitDefaultTestSuitePath('HasSingleBeforeEachHook'))->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['beforeEach', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['beforeEach', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteHasSingleAfterAllHook() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->implicitDefaultTestSuitePath('HasSingleAfterAllHook'))->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['afterAll', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['afterAll', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteHasSingleAfterEachHook() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->implicitDefaultTestSuitePath('HasSingleAfterEachHook'))->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['ensureSomething', 'afterEach'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestProcessedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(TestResult::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getTestMethod());
            $this->assertEquals(['ensureSomethingTwice', 'afterEach'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingTest() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingTest');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            /** @var TestProcessedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(TestResult::class, $testInvokedEvent->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('throwsException', $testInvokedEvent->getTarget()->getTestMethod());

            $this->assertNotNull($testInvokedEvent->getTarget()->getException());
            $expectedMsg = 'An unexpected exception of type "Exception" with code 0 and message "Test failure" was thrown from #[Test] ' . ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class . '::throwsException';
            $this->assertSame($expectedMsg, $testInvokedEvent->getTarget()->getException()->getMessage());
            $this->assertSame(0, $testInvokedEvent->getTarget()->getException()->getCode());
            $this->assertInstanceOf(Exception::class, $testInvokedEvent->getTarget()->getException()->getPrevious());
            $this->assertSame('Test failure', $testInvokedEvent->getTarget()->getException()->getPrevious()->getMessage());
            $this->assertSame($dir . '/MyTestCase.php', $testInvokedEvent->getTarget()->getException()->getPrevious()->getFile());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingTestWithAfterEachHook() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingTestWithAfterEachHook');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            /** @var TestProcessedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(TestResult::class, $testInvokedEvent->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\ExceptionThrowingTestWithAfterEachHook\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('throwsException', $testInvokedEvent->getTarget()->getTestMethod());
            $this->assertTrue($testInvokedEvent->getTarget()->getTestCase()->getAfterHookCalled());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingBeforeAll() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingBeforeAll');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestCaseSetUpException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingBeforeAll\MyTestCase::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::beforeAll" #[BeforeAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the class beforeAll".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingAfterAll() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingAfterAll');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestCaseTearDownException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingAfterAll\MyTestCase::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::afterAll" #[AfterAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the class afterAll".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingBeforeEach() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingBeforeEach');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestSetUpException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingBeforeEach\MyTestCase::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::beforeEach" #[BeforeEach] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the object beforeEach".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingAfterEach() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingAfterEach');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestTearDownException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingAfterEach\MyTestCase::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::afterEach" #[AfterEach] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the object afterEach".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeAll() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeAll');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestSuiteSetUpException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeAll\MyTestSuite::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::throwException" #[BeforeAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in AttachToTestSuite".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeEach() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeEach');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestSuiteSetUpException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEach\MyTestSuite::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::throwEachException" #[BeforeEach] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite BeforeEach".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterEach() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterEach');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestSuiteTearDownException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterEach\MyTestSuite::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwEachException" #[AfterEach] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterEach".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterEachTest() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterEachTest');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestTearDownException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterEachTest\MyTestSuite::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwEachTestException" #[AfterEachTest] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterEachTest".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeEachTest() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeEachTest');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestSetUpException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEachTest\MyTestSuite::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::throwEachTestException" #[BeforeEachTest] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite BeforeEachTest".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterAll() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterAll');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();

            $this->expectException(TestSuiteTearDownException::class);
            $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterAll\MyTestSuite::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwException" #[AfterAll] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterAll".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteTestFailedExceptionThrowingTest() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestFailedExceptionThrowingTest');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            /** @var TestProcessedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(TestResult::class, $testInvokedEvent->getTarget());
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

    public function testImplicitDefaultTestSuiteCustomAssertions() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('CustomAssertions');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

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

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            /** @var TestProcessedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestProcessedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(TestResult::class, $testInvokedEvent->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\CustomAssertions\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('ensureCustomAssertionsPass', $testInvokedEvent->getTarget()->getTestMethod());

            $this->assertNull($testInvokedEvent->getTarget()->getException());
        });
    }

    public function testImplicitDefaultTestSuiteHasDataProvider() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('HasDataProvider');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(3, $state->events);

            /** @var TestProcessedEvent $firstEvent */
            $firstEvent = $state->events[0];
            $this->assertSame(1, $firstEvent->getTarget()->getTestCase()->getCounter());
        });
    }

    public function testExplicitTestSuiteDefaultExplicitTestSuite() {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('AnnotatedDefaultTestSuite');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];

            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            $this->assertNull($state->events[0]->getTarget()->getException());
            $this->assertSame(ExplicitTestSuite\AnnotatedDefaultTestSuite\MyTestSuite::class, $state->events[0]->getTarget()->getTestCase()->getTestSuiteName());
        });
    }

    public function testImplicitDefaultTestSuiteMultipleBeforeAllHooks() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('MultipleBeforeAllHooks');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                 $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
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

    public function testExplicitTestSuiteBeforeAllTestSuiteHook() : void {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('BeforeAllTestSuiteHook');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                 $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
            $this->assertCount(3, $state->events);

            $testSuite = $state->events[0]->getTarget()->getTestCase()->testSuite();
            foreach ($state->events as $testInvokedEvent) {
                $this->assertSame($testSuite, $testInvokedEvent->getTarget()->getTestCase()->testSuite());
            }

            $allResults = array_map(fn(TestProcessedEvent $event) => $event->getTarget()->getException(), $state->events);
            $this->assertSame([null, null, null], $allResults);
        });
    }

    public function testExplicitTestSuiteBeforeEachTestSuiteHook() : void {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('BeforeEachTestSuiteHook');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            $this->assertCount(1, $testSuites);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
            $this->assertCount(6, $state->events);

            $testSuite = $state->events[0]->getTarget()->getTestCase()->testSuite();
            foreach ($state->events as $testInvokedEvent) {
                $this->assertSame($testSuite, $testInvokedEvent->getTarget()->getTestCase()->testSuite());
            }
            $expected = ['AsyncUnit', 'AsyncUnit', 'AsyncUnit'];
            $this->assertSame($expected, $testSuite->getState());
        });
    }

    public function testExplicitTestSuiteBeforeEachTestTestSuiteHook() : void {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('BeforeEachTestTestSuiteHook');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            $this->assertCount(1, $testSuites);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
            $this->assertCount(6, $state->events);

            $testSuite = $state->events[0]->getTarget()->getTestCase()->testSuite();
            foreach ($state->events as $testInvokedEvent) {
                $this->assertSame($testSuite, $testInvokedEvent->getTarget()->getTestCase()->testSuite());
            }
            $expected = ['AsyncUnit', 'AsyncUnit', 'AsyncUnit', 'AsyncUnit', 'AsyncUnit', 'AsyncUnit'];
            $this->assertSame($expected, $testSuite->getState());
        });
    }

    public function testExplicitTestSuiteAfterEachTestTestSuiteHook() : void {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('AfterEachTestTestSuiteHook');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            $this->assertCount(1, $testSuites);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
            $this->assertCount(6, $state->events);

            $testSuite = $state->events[0]->getTarget()->getTestCase()->testSuite();
            foreach ($state->events as $testInvokedEvent) {
                $this->assertSame($testSuite, $testInvokedEvent->getTarget()->getTestCase()->testSuite());
            }
            $expected = ['AsyncUnit', 'AsyncUnit', 'AsyncUnit', 'AsyncUnit', 'AsyncUnit', 'AsyncUnit'];
            $this->assertSame($expected, $testSuite->getState());
        });
    }

    public function testExplicitTestSuiteAfterEachTestSuiteHook() : void {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('AfterEachTestSuiteHook');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            $this->assertCount(1, $testSuites);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
            $this->assertCount(6, $state->events);

            $testSuite = $state->events[0]->getTarget()->getTestCase()->testSuite();
            foreach ($state->events as $testInvokedEvent) {
                $this->assertSame($testSuite, $testInvokedEvent->getTarget()->getTestCase()->testSuite());
            }
            $expected = ['AsyncUnit', 'AsyncUnit', 'AsyncUnit'];
            $this->assertSame($expected, $testSuite->getState());
        });
    }

    public function testExplicitTestSuiteAfterAllTestSuiteHook() : void {
        Loop::run(function() {
            $dir = $this->explicitTestSuitePath('AfterAllTestSuiteHook');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
            $this->assertCount(3, $state->events);

            $testSuite = $state->events[0]->getTarget()->getTestCase()->testSuite();
            foreach ($state->events as $testInvokedEvent) {
                $this->assertSame($testSuite, $testInvokedEvent->getTarget()->getTestCase()->testSuite());
            }
            $allResults = array_map(fn(TestProcessedEvent $event) => $event->getTarget()->getException(), $state->events);
            $this->assertSame([null, null, null], $allResults);
            $this->assertSame(1, $testSuite->getCounter());
        });
    }

    public function testTestPassedEventsEmitted() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('SingleTest');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->data = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use($state) {
                $state->data[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_PASSED, function() use($state) {
                $state->data[] = 'test passed';
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertSame(['test invoked', 'test passed'], $state->data);
        });
    }

    public function testTestFailedEventsEmitted() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('FailedAssertion');

            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->data = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use($state) {
                $state->data[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_FAILED, function() use($state) {
                $state->data[] = 'test failed';
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertSame(['test invoked', 'test failed'], $state->data);
        });
    }

    public function testTestSuiteProcessingEventEmitted() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('SingleTest');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_SUITE_STARTED, function($event) use($state) {
                $state->events[] = $event;
            });
            $this->emitter->on(Events::TEST_SUITE_FINISHED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);
            $this->assertInstanceOf(TestSuiteStartedEvent::class, $state->events[0]);
            $this->assertInstanceOf(TestSuiteFinishedEvent::class, $state->events[1]);
        });
    }

    public function testTestCaseProcessingEventEmitted() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('SingleTest');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_CASE_STARTED, function($event) use($state) {
                $state->events[] = $event;
            });
            $this->emitter->on(Events::TEST_CASE_FINISHED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);
            $this->assertInstanceOf(TestCaseStartedEvent::class, $state->events[0]);
            $this->assertInstanceOf(TestCaseFinishedEvent::class, $state->events[1]);
        });
    }

    public function testTestMethodIsNotInvokedWhenDisabled() : void {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestDisabled');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);
        });
    }

    public function testTestMethodIsNotInvokedWhenTestCaseDisabled() : void {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestCaseDisabled');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

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
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
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
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PASSED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
        });
    }

    public function testImplicitDefaultTestSuiteTestDisabledHookNotInvoked() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestDisabledHookNotInvoked');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

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
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

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
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);

            $testSomethingEvent = $this->fetchTestProcessedEventForTest($state->events, ExplicitTestSuite\TestSuiteDisabledHookNotInvoked\MyTestCase::class, 'testSomething');

            $this->assertSame(TestState::Disabled(), $testSomethingEvent->getTarget()->getState());

            $this->assertSame([], $testSomethingEvent->getTarget()->getTestCase()->testSuite()->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestDisabledCustomMessage() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('TestDisabledCustomMessage');
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

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
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

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
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PROCESSED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

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
            $testSuites = $this->parser->parse($dir)->getTestSuiteModels();
            $state = new stdClass();
            $state->events = [];
            $this->emitter->on(Events::TEST_PASSED, fn($event) => $state->events[] = $event);
            $this->emitter->on(Events::TEST_FAILED, fn($event) => $state->events[] = $event);
            $this->emitter->on(Events::TEST_DISABLED, fn($event) => $state->events[] = $event);

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

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

    private function fetchTestProcessedEventForTest(array $events, string $testClass, string $method) : Event {
        foreach ($events as $event) {
            if ($event->getTarget()->getTestCase()::class === $testClass && $event->getTarget()->getTestMethod() === $method) {
                return $event;
            }
        }
        $this->fail('Expected events to have a TestCase and method matching ' . $testClass . '::' . $method);
    }
}