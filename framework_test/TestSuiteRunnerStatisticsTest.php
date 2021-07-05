<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Cspray\Labrador\AsyncUnit\Event\ProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\ProcessingStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestCaseFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestDisabledEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteFinishedEvent;
use Cspray\Labrador\AsyncUnit\MockBridge\MockeryMockBridge;
use Cspray\Labrador\AsyncUnit\Statistics\AggregateSummary;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use Cspray\Labrador\AsyncUnit\Stub\MockBridgeFactoryStub;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use stdClass;

class TestSuiteRunnerStatisticsTest extends PHPUnitTestCase {

    use UsesAcmeSrc;
    use TestSuiteRunnerScaffolding;

    public function setUp(): void {
        $this->buildTestSuiteRunner();
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

    public function processedTestSuiteSummaryTestSuiteNameProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTestSuiteNameProvider
     */
    public function testProcessedTestSuiteSummaryHasCorrectTestSuiteName(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $state = new stdClass();
            $state->data = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use($state) {
                $state->data[] = $event->getTarget()->getTestSuiteName();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEqualsCanonicalizing($expected, $state->data);
        });
    }

    public function processedTestSuiteSummaryTestCaseNamesProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => [ImplicitDefaultTestSuite\SingleTest\MyTestCase::class]
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class => [
                    ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class,
                    ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class,
                ],
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class => [
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class,
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class,
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class,
                ],
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class
                ]
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => [
                    ExplicitTestSuite\TestSuiteDisabled\FirstTestCase::class,
                    ExplicitTestSuite\TestSuiteDisabled\SecondTestCase::class
                ]
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTestCaseNamesProvider
     */
    public function testProcessedTestSuiteSummaryHasTestCaseNames(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getTestCaseNames();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function processedTestSuiteSummaryTotalTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [
                ImplicitTestSuite::class => 3
            ]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 2
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTotalTestCaseCountProvider
     */
    public function testProcessedTestSuiteSummaryHasTotalTestCaseCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getTestCaseCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestSuiteSummaryDisabledTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 0,
            ]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 2
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryDisabledTestCaseCountProvider
     */
    public function testProcessedTestSuiteSummaryHasDisabledTestCaseCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getDisabledTestCaseCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestSuiteSummaryTotalTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 3
            ]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 3
            ]],
            [$this->implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 2
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTotalTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasTotalTestCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getTestCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestSuiteSummaryDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 0,
            ]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 3
            ]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 3
            ]],
            [$this->implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 1
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryDisabledTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasDisabledTestCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getDisabledTestCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestSuiteSummaryPassedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class => 1,]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 8]],
            [$this->implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 1
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryPassedTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasPassedTestCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getPassedTestCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestSuiteSummaryFailedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 1,]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 1]],
            [$this->implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 1]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryFailedTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasFailedTestCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getFailedTestCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestSuiteSummaryAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 1,]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 18]],
            [$this->implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 1]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryAssertionCountProvider
     */
    public function testProcessedTestSuiteSummaryHasAssertionCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getAssertionCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestSuiteSummaryAsyncAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 0,]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 4]],
            [$this->implicitDefaultTestSuitePath('SingleTestAsyncAssertion'), [ImplicitTestSuite::class => 1]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryAsyncAssertionCountProvider
     */
    public function testProcessedTestSuiteSummaryHasAsyncAssertionCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function(TestSuiteFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getAsyncAssertionCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestCaseSummaryTestSuiteNameProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => ImplicitTestSuite::class
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => ImplicitTestSuite::class
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryTestSuiteNameProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectTestSuiteName(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getTestSuiteName();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            ksort($expected);
            ksort($actual);
            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestCaseSummaryTestNamesProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => [
                    ImplicitDefaultTestSuite\SingleTest\MyTestCase::class . '::ensureSomethingHappens'
                ]
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::testOne',
                    ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::testTwo',
                    ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::disabledTest'
                ],
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class . '::checkTwo',
                    ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class . '::checkTwoDisabled'
                ],
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class . '::isBestHobbit',
                ],
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class . '::isBestHobbit'
                ],
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class . '::isBestHobbit'
                ],
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class . '::checkFood#0',
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class . '::checkFood#1',
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class . '::checkFood#2',
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class . '::checkFood#3'
                ]
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryTestNamesProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectTestNames(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getTestNames();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            ksort($expected);
            ksort($actual);
            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestCaseSummaryTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 3,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 2,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectTestCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getTestCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            ksort($expected);
            ksort($actual);
            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestCaseSummaryDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryDisabledTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectDisabledTestCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getDisabledTestCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            ksort($expected);
            ksort($actual);
            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestCaseSummaryPassedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 2,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryPassedTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectPassedTestCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getPassedTestCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            ksort($expected);
            ksort($actual);
            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestCaseSummaryFailedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryFailedTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectFailedTestCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getFailedTestCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            ksort($expected);
            ksort($actual);
            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestCaseSummaryAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class =>1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryAssertionCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectAssertionCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getAssertionCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            ksort($expected);
            ksort($actual);
            $this->assertEquals($expected, $actual);
        });
    }

    public function processedTestCaseSummaryAsyncAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('SingleTestAsyncAssertion'), [
                ImplicitDefaultTestSuite\SingleTestAsyncAssertion\MyTestCase::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class =>1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryAsyncAssertionCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectAsyncAssertionCount(string $path, array $expected) : void {
        Loop::run(function() use($path, $expected) {
            $results = yield $this->parser->parse($path);
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getAsyncAssertionCount();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            ksort($expected);
            ksort($actual);
            $this->assertEquals($expected, $actual);
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

    public function testTestSuiteSummaryHasDuration() : void {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
            $state = new stdClass();
            $state->event = null;

            $this->emitter->on(Events::TEST_SUITE_FINISHED, function($event) use($state) {
                $state->event = $event;
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertInstanceOf(TestSuiteFinishedEvent::class, $state->event);
            $this->assertGreaterThan(600, $state->event->getTarget()->getDuration()->asMilliseconds());
        });
    }

    public function testTestCaseSummaryHasDuration() : void {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
            $actual = [];

            $this->emitter->on(Events::TEST_CASE_FINISHED, function(TestCaseFinishedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getDuration()->asMilliseconds();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $expected = [
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\FirstTestCase::class => 99,
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\SecondTestCase::class => 199,
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class => 299
            ];

            foreach ($expected as $testCase => $duration) {
                $this->assertGreaterThanOrEqual($duration, $actual[$testCase]);
            }
        });
    }

    public function testTestResultHasDuration() : void {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
            $actual = [];

            $this->emitter->on(Events::TEST_PROCESSED, function(TestProcessedEvent $event) use(&$actual) {
                $actual[$event->getTarget()->getTestCase()::class . '::' . $event->getTarget()->getTestMethod()] = $event->getTarget()->getDuration()->asMilliseconds();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $expected = [
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\FirstTestCase::class . '::checkOne' => 99,
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\SecondTestCase::class . '::checkOne' => 99,
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\SecondTestCase::class . '::checkTwo' => 99,
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class . '::checkOne' => 99,
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class . '::checkTwo' => 99,
                ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class . '::checkThree' => 99
            ];

            foreach ($expected as $testCase => $duration) {
                $this->assertGreaterThanOrEqual($duration, $actual[$testCase], $testCase . ' did not execute long enough');
            }
        });
    }

    public function testDisabledTestHasZeroDuration() : void {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('TestDisabled'));
            $actual = [];

            $this->emitter->on(Events::TEST_DISABLED, function(TestDisabledEvent $event) use(&$actual) {
                $actual[] = $event->getTarget()->getDuration()->asMilliseconds();
            });

            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertCount(1, $actual);
            $this->assertSame(0.0, $actual[0]);
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

    public function testTestCaseSummaryMockBridgeAssertionCount() {
        Loop::run(function() {
            $results = yield $this->parser->parse($this->implicitDefaultTestSuitePath('MockeryTestNoAssertion'));
            $state = new stdClass();
            $state->event = null;
            $this->emitter->on(Events::TEST_PROCESSED, function($event) use($state) {
                 $state->event = $event;
            });

            $this->testSuiteRunner->setMockBridgeClass(MockeryMockBridge::class);
            yield $this->testSuiteRunner->runTestSuites($results);

            $this->assertInstanceOf(TestProcessedEvent::class, $state->event);
            $this->assertEquals(1, $state->event->getTarget()->getTestCase()->getAssertionCount());
        });
    }
}