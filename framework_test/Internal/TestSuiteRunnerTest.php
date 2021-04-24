<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal;

use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionResult;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestSetupException;
use Cspray\Labrador\AsyncUnit\Exception\TestTearDownException;
use Cspray\Labrador\AsyncUnit\Internal\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncUnit\Internal\Model\InvokedTestCaseTestModel;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\TestSuiteRunner
 */
class TestSuiteRunnerTest extends PHPUnitTestCase {

    // The TestSuiteRunner assumes some other thing controlling it has started the loop

    private string $acmeSrcDir;
    private Parser $parser;
    private EventEmitter $emitter;
    private CustomAssertionContext $customAssertionContext;
    private TestSuiteRunner $testSuiteRunner;

    public function setUp() : void {
        $this->acmeSrcDir = dirname(__DIR__, 2) . '/acme_src';
        $this->parser = new Parser();
        $this->emitter = new AmpEventEmitter();
        $this->customAssertionContext = (new \ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $this->testSuiteRunner = new TestSuiteRunner($this->emitter, $this->customAssertionContext);
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTestInvokesMethod() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/SingleTest');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            $this->assertInstanceOf(TestInvokedEvent::class, $state->events[0]);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $state->events[0]->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $state->events[0]->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappens', $state->events[0]->getTarget()->getMethod());
            $this->assertTrue($state->events[0]->getTarget()->getTestCase()->getTestInvoked());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteMultipleTestInvokesMethod() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/MultipleTest');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(3, $state->events);

            $ensureSomethingHappensMethod = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingHappensMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingHappensMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class, $ensureSomethingHappensMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappens', $ensureSomethingHappensMethod->getTarget()->getMethod());
            $this->assertEquals([ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappens'], $ensureSomethingHappensMethod->getTarget()->getTestCase()->getInvokedMethods());
            
            $ensureSomethingHappensTwiceMethod = $state->events[1];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingHappensTwiceMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingHappensTwiceMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class, $ensureSomethingHappensTwiceMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingHappensTwice', $ensureSomethingHappensTwiceMethod->getTarget()->getMethod());
            $this->assertEquals([ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappensTwice'], $ensureSomethingHappensTwiceMethod->getTarget()->getTestCase()->getInvokedMethods());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteHasSingleBeforeAllHook() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/HasSingleBeforeAllHook');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getMethod());
            $this->assertEquals(['beforeAll', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getMethod());
            $this->assertEquals(['beforeAll', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteHasSingleBeforeEachHook() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/HasSingleBeforeEachHook');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getMethod());
            $this->assertEquals(['beforeEach', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleBeforeEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getMethod());
            $this->assertEquals(['beforeEach', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteHasSingleAfterAllHook() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/HasSingleAfterAllHook');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getMethod());
            $this->assertEquals(['afterAll', 'ensureSomething'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterAllHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getMethod());
            $this->assertEquals(['afterAll', 'ensureSomethingTwice'], $ensureSomethingMethod->getTarget()->getTestCase()->getCombinedData());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteHasSingleAfterEachHook() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/HasSingleAfterEachHook');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(2, $state->events);

            $ensureSomethingMethod = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomething', $ensureSomethingMethod->getTarget()->getMethod());
            $this->assertEquals(['ensureSomething', 'afterEach'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());

            $ensureSomethingMethod = $state->events[1];
            $this->assertInstanceOf(TestInvokedEvent::class, $ensureSomethingMethod);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $ensureSomethingMethod->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\HasSingleAfterEachHook\MyTestCase::class, $ensureSomethingMethod->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingTwice', $ensureSomethingMethod->getTarget()->getMethod());
            $this->assertEquals(['ensureSomethingTwice', 'afterEach'], $ensureSomethingMethod->getTarget()->getTestCase()->getData());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingTest() {
        Loop::run(function() {
            $dir = $this->acmeSrcDir . '/ImplicitDefaultTestSuite/ExceptionThrowingTest';
            $testSuites = $this->parser->parse($dir);
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            /** @var TestInvokedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $testInvokedEvent->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('throwsException', $testInvokedEvent->getTarget()->getMethod());

            $this->assertNotNull($testInvokedEvent->getTarget()->getFailureException());
            $expectedMsg = 'An unexpected exception of type "Exception" with code 0 and message "Test failure" was thrown from #[Test] ' . ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class . '::throwsException';
            $this->assertSame($expectedMsg, $testInvokedEvent->getTarget()->getFailureException()->getMessage());
            $this->assertSame(0, $testInvokedEvent->getTarget()->getFailureException()->getCode());
            $this->assertInstanceOf(\Exception::class, $testInvokedEvent->getTarget()->getFailureException()->getPrevious());
            $this->assertSame('Test failure', $testInvokedEvent->getTarget()->getFailureException()->getPrevious()->getMessage());
            $this->assertSame($dir . '/MyTestCase.php', $testInvokedEvent->getTarget()->getFailureException()->getPrevious()->getFile());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingTestWithAfterEachHook() {
        Loop::run(function() {
            $dir = $this->acmeSrcDir . '/ImplicitDefaultTestSuite/ExceptionThrowingTestWithAfterEachHook';
            $testSuites = $this->parser->parse($dir);
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            /** @var TestInvokedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $testInvokedEvent->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\ExceptionThrowingTestWithAfterEachHook\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('throwsException', $testInvokedEvent->getTarget()->getMethod());
            $this->assertTrue($testInvokedEvent->getTarget()->getTestCase()->getAfterHookCalled());
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingBeforeAll() {
        Loop::run(function() {
            $dir = $this->acmeSrcDir . '/ImplicitDefaultTestSuite/ExceptionThrowingBeforeAll';
            $testSuites = $this->parser->parse($dir);
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function ($event) use ($state) {
                $state->events[] = $event;
            });

            $this->expectException(TestCaseSetUpException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingBeforeAll\MyTestCase::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::beforeAll" #[BeforeAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the class beforeAll".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingAfterAll() {
        Loop::run(function() {
            $dir = $this->acmeSrcDir . '/ImplicitDefaultTestSuite/ExceptionThrowingAfterAll';
            $testSuites = $this->parser->parse($dir);
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function ($event) use ($state) {
                $state->events[] = $event;
            });

            $this->expectException(TestCaseTearDownException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingAfterAll\MyTestCase::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::afterAll" #[AfterAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the class afterAll".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingBeforeEach() {
        Loop::run(function() {
            $dir = $this->acmeSrcDir . '/ImplicitDefaultTestSuite/ExceptionThrowingBeforeEach';
            $testSuites = $this->parser->parse($dir);
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function ($event) use ($state) {
                $state->events[] = $event;
            });

            $this->expectException(TestSetUpException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingBeforeEach\MyTestCase::class;
            $this->expectExceptionMessage('Failed setting up "' . $class . '::beforeEach" #[BeforeEach] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the object beforeEach".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteExceptionThrowingAfterEach() {
        Loop::run(function() {
            $dir = $this->acmeSrcDir . '/ImplicitDefaultTestSuite/ExceptionThrowingAfterEach';
            $testSuites = $this->parser->parse($dir);
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function ($event) use ($state) {
                $state->events[] = $event;
            });

            $this->expectException(TestTearDownException::class);
            $class = ImplicitDefaultTestSuite\ExceptionThrowingAfterEach\MyTestCase::class;
            $this->expectExceptionMessage('Failed tearing down "' . $class . '::afterEach" #[AfterEach] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the object afterEach".');

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);
        });
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteTestFailedExceptionThrowingTest() {
        Loop::run(function() {
            $dir = $this->acmeSrcDir . '/ImplicitDefaultTestSuite/TestFailedExceptionThrowingTest';
            $testSuites = $this->parser->parse($dir);
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
                $state->events[] = $event;
            });

            yield $this->testSuiteRunner->runTestSuites(...$testSuites);

            $this->assertCount(1, $state->events);
            /** @var TestInvokedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $testInvokedEvent->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\TestFailedExceptionThrowingTest\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('ensureSomethingFails', $testInvokedEvent->getTarget()->getMethod());

            $this->assertNotNull($testInvokedEvent->getTarget()->getFailureException());
            $expectedMsg = 'Something barfed';
            $this->assertSame($expectedMsg, $testInvokedEvent->getTarget()->getFailureException()->getMessage());
            $this->assertSame(0, $testInvokedEvent->getTarget()->getFailureException()->getCode());
            $this->assertSame($dir . '/MyTestCase.php', $testInvokedEvent->getTarget()->getFailureException()->getFile());
        });
    }

    public function testImplicitDefaultTestSuiteCustomAssertions() {
        Loop::run(function() {
            $dir = $this->acmeSrcDir . '/ImplicitDefaultTestSuite/CustomAssertions';
            $testSuites = $this->parser->parse($dir);
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(InternalEventNames::TEST_INVOKED, function($event) use($state) {
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
            /** @var TestInvokedEvent $testInvokedEvent */
            $testInvokedEvent = $state->events[0];
            $this->assertInstanceOf(TestInvokedEvent::class, $testInvokedEvent);
            $this->assertInstanceOf(InvokedTestCaseTestModel::class, $testInvokedEvent->getTarget());
            $this->assertInstanceOf(ImplicitDefaultTestSuite\CustomAssertions\MyTestCase::class, $testInvokedEvent->getTarget()->getTestCase());
            $this->assertSame('ensureCustomAssertionsPass', $testInvokedEvent->getTarget()->getMethod());

            $this->assertNull($testInvokedEvent->getTarget()->getFailureException());
        });
    }

}