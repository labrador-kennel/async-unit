<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Parser\StaticAnalysisParser;
use Cspray\Labrador\AsyncUnit\Stub\MockBridgeFactoryStub;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

trait TestSuiteRunnerScaffolding {

    private StaticAnalysisParser $parser;
    private EventEmitter $emitter;
    private CustomAssertionContext $customAssertionContext;
    private TestSuiteRunner $testSuiteRunner;
    private MockBridgeFactory $mockBridgeFactory;

    public function buildTestSuiteRunner(MockBridgeFactory $mockBridgeFactory) : void {
        $this->parser = new StaticAnalysisParser();
        $this->emitter = new AmpEventEmitter();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->customAssertionContext = (new ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $this->mockBridgeFactory = $mockBridgeFactory;
        $this->testSuiteRunner = new TestSuiteRunner(
            $this->emitter,
            $this->customAssertionContext,
            new NullRandomizer(),
            $this->mockBridgeFactory
        );
    }

}