<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;
use Amp\Loop;
use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncTesting\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncTesting\Internal\Model\InvokedTestCaseTestModel;
use Cspray\Labrador\AsyncTesting\Internal\Parser;
use Acme\DemoSuites\SimpleTestCase\ImplicitDefaultTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @covers \Cspray\Labrador\AsyncTesting\TestSuiteRunner
 */
class TestSuiteRunnerTest extends PHPUnitTestCase {

    // The TestSuiteRunner assumes some other thing controlling it has started the loop

    private string $acmeSrcDir;
    private Parser $parser;
    private EventEmitter $emitter;
    private TestSuiteRunner $testSuiteRunner;

    public function setUp() : void {
        $this->acmeSrcDir = dirname(__DIR__, 1) . '/acme_src';
        $this->parser = new Parser();
        $this->emitter = new AmpEventEmitter();
        $this->testSuiteRunner = new TestSuiteRunner($this->emitter);
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTestInvokesMethod() {
        Loop::run(function() {
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/SingleTest');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(EventNames::TEST_INVOKED, function($event) use($state) {
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
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/MultipleTest');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(EventNames::TEST_INVOKED, function($event) use($state) {
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
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/HasSingleBeforeAllHook');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(EventNames::TEST_INVOKED, function($event) use($state) {
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
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/HasSingleBeforeEachHook');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(EventNames::TEST_INVOKED, function($event) use($state) {
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
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/HasSingleAfterAllHook');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(EventNames::TEST_INVOKED, function($event) use($state) {
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
            $testSuites = $this->parser->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/HasSingleAfterEachHook');
            $state = new \stdClass();
            $state->events = [];

            $this->emitter->on(EventNames::TEST_INVOKED, function($event) use($state) {
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
}