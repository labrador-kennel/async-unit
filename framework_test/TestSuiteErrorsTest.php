<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestSetupException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestTearDownException;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use Cspray\Labrador\AsyncUnit\Stub\MockBridgeFactoryStub;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestSuiteErrorsTest extends PHPUnitTestCase {

    use UsesAcmeSrc;
    use TestSuiteRunnerScaffolding;

    public function setUp(): void {
        $this->buildTestSuiteRunner();
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

}