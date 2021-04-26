<?php

namespace Cspray\Labrador\AsyncUnit;

use Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin\MyCustomAssertionPlugin;
use Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin\MyOtherCustomAssertionPlugin;
use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\Internal\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Internal\Model\TestSuiteModel;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Internal\Parser
 */
class ParserTest extends PHPUnitTestCase {

    use AsyncUnitAssertions;

    private string $acmeSrcDir;
    private Parser $subject;

    public function setUp() : void {
        $this->acmeSrcDir = dirname(__DIR__) . '/acme_src';
        $this->subject = new Parser();
    }

    public function testErrorConditionsNoTestsTestCase() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\NoTestsTestCase\\BadTestCase". There were no #[Test] found.');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/NoTestsTestCase');
    }

    public function testErrorConditionsBeforeAllNonStaticMethod() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\BeforeAllNonStaticMethod\\BadTestCase". The non-static method "badBeforeAllMustBeStatic" cannot be used as a #[BeforeAll] hook.');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/BeforeAllNonStaticMethod');
    }

    public function testErrorConditionsAfterAllNonStaticMethod() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\AfterAllNonStaticMethod\\BadTestCase". The non-static method "badAfterAllMustBeStatic" cannot be used as a #[AfterAll] hook.');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/AfterAllNonStaticMethod');
    }

    public function testErrorConditionsTestAttributeOnNotTestCase() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\TestAttributeOnNotTestCase\\BadTestCase". The method "ensureSomething" is annotated with AsyncUnit attributes but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/TestAttributeOnNotTestCase');
    }

    public function testErrorConditionsBeforeAllAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\BeforeAllAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is annotated with AsyncUnit attributes but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/BeforeAllAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsAfterAllAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\AfterAllAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is annotated with AsyncUnit attributes but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/AfterAllAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsAfterEachAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\AfterEachAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is annotated with AsyncUnit attributes but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/AfterEachAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsBeforeEachAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\BeforeEachAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is annotated with AsyncUnit attributes but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/BeforeEachAttributeOnNotTestCaseOrTestSuite');
    }

    public function testDefaultTestSuiteName() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/SingleTest')->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertSame('Default TestSuite', $testSuite->getName());
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteSingleTest() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/SingleTest')->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $expectedTestCase = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\SingleTest\\MyTestCase';
        $this->assertCount(1, $testSuite->getTestCaseModels());
        $this->assertTestCaseClassBelongsToTestSuite($expectedTestCase, $testSuite);

        $testCaseModel = $this->fetchTestCaseModel($testSuite, $expectedTestCase);

        $this->assertCount(1, $testCaseModel->getTestMethodModels());
        $this->assertTestMethodBelongsToTestCase($expectedTestCase . '::ensureSomethingHappens', $testCaseModel);
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteMultipleTest() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/MultipleTest')->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];
        $expectedTestCase = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\MultipleTest\\MyTestCase';
        $this->assertCount(1, $testSuite->getTestCaseModels());
        $this->assertTestCaseClassBelongsToTestSuite(
            $expectedTestCase,
            $testSuite
        );

        $testCase = $this->fetchTestCaseModel($testSuite, $expectedTestCase);

        $this->assertCount(3, $testCase->getTestMethodModels());
        $this->assertTestMethodBelongsToTestCase($expectedTestCase . '::ensureSomethingHappens', $testCase);
        $this->assertTestMethodBelongsToTestCase($expectedTestCase . '::ensureSomethingHappensTwice', $testCase);
        $this->assertTestMethodBelongsToTestCase($expectedTestCase . '::ensureSomethingHappensThreeTimes', $testCase);
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteHasNotTestCaseObject() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/HasNotTestCaseObject')->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $expectedTestCase = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\HasNotTestCaseObject\\MyTestCase';
        $this->assertCount(1, $testSuite->getTestCaseModels());
        $this->assertTestCaseClassBelongsToTestSuite(
            $expectedTestCase,
            $testSuite
        );

        $testCase = $this->fetchTestCaseModel($testSuite, $expectedTestCase);

        $this->assertCount(1, $testCase->getTestMethodModels());
        $this->assertTestMethodBelongsToTestCase($expectedTestCase . '::ensureSomethingHappens', $testCase);
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteMultipleTestCase() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/MultipleTestCase')->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $barTestCaseClass = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\MultipleTestCase\\BarTestCase';
        $bazTestCaseClass = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\MultipleTestCase\\BazTestCase';
        $fooTestCaseClass = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\MultipleTestCase\\FooTestCase';

        $this->assertCount(3, $testSuite->getTestCaseModels());
        $this->assertTestCaseClassBelongsToTestSuite($barTestCaseClass, $testSuite);
        $this->assertTestCaseClassBelongsToTestSuite($bazTestCaseClass, $testSuite);
        $this->assertTestCaseClassBelongsToTestSuite($fooTestCaseClass, $testSuite);

        $barTestCase = $this->fetchTestCaseModel($testSuite, $barTestCaseClass);
        $bazTestCase = $this->fetchTestCaseModel($testSuite, $bazTestCaseClass);
        $fooTestCase = $this->fetchTestCaseModel($testSuite, $fooTestCaseClass);

        $this->assertCount(1, $barTestCase->getTestMethodModels());
        $this->assertCount(1, $bazTestCase->getTestMethodModels());
        $this->assertCount(2, $fooTestCase->getTestMethodModels());
    }

    public function testParsingImplicitDefaultTestSuiteExtendedTestCases() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/ExtendedTestCases')->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $firstTestCaseClass = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\ExtendedTestCases\\FirstTestCase';
        $thirdTestCaseClass = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\ExtendedTestCases\\ThirdTestCase';
        $fifthTestCaseClass = 'Acme\\DemoSuites\\ImplicitDefaultTestSuite\\ExtendedTestCases\\FifthTestCase';

        $this->assertCount(3, $testSuite->getTestCaseModels());
        $this->assertTestCaseClassBelongsToTestSuite($firstTestCaseClass, $testSuite);
        $this->assertTestCaseClassBelongsToTestSuite($thirdTestCaseClass, $testSuite);
        $this->assertTestCaseClassBelongsToTestSuite($fifthTestCaseClass, $testSuite);

        $firstTestCase = $this->fetchTestCaseModel($testSuite, $firstTestCaseClass);
        $this->assertCount(1, $firstTestCase->getTestMethodModels());
        $this->assertTestMethodBelongsToTestCase($firstTestCaseClass . '::firstEnsureSomething', $firstTestCase);

        $thirdTestCase = $this->fetchTestCaseModel($testSuite, $thirdTestCaseClass);
        $this->assertCount(3, $thirdTestCase->getTestMethodModels());
        $this->assertTestMethodBelongsToTestCase($thirdTestCaseClass . '::firstEnsureSomething', $thirdTestCase);
        $this->assertTestMethodBelongsToTestCase($thirdTestCaseClass . '::secondEnsureSomething', $thirdTestCase);
        $this->assertTestMethodBelongsToTestCase($thirdTestCaseClass . '::thirdEnsureSomething', $thirdTestCase);

        $fifthTestCase = $this->fetchTestCaseModel($testSuite, $fifthTestCaseClass);
        $this->assertCount(5, $fifthTestCase->getTestMethodModels());
        $this->assertTestMethodBelongsToTestCase($fifthTestCaseClass . '::firstEnsureSomething', $fifthTestCase);
        $this->assertTestMethodBelongsToTestCase($fifthTestCaseClass . '::secondEnsureSomething', $fifthTestCase);
        $this->assertTestMethodBelongsToTestCase($fifthTestCaseClass . '::thirdEnsureSomething', $fifthTestCase);
        $this->assertTestMethodBelongsToTestCase($fifthTestCaseClass . '::fourthEnsureSomething', $fifthTestCase);
        $this->assertTestMethodBelongsToTestCase($fifthTestCaseClass . '::fifthEnsureSomething', $fifthTestCase);
    }

    public function hooksProvider() {
        return [
            ['getBeforeAllMethodModels', 'HasSingleBeforeAllHook', 'beforeAll'],
            ['getBeforeEachMethodModels', 'HasSingleBeforeEachHook', 'beforeEach'],
            ['getAfterAllMethodModels', 'HasSingleAfterAllHook', 'afterAll'],
            ['getAfterEachMethodModels', 'HasSingleAfterEachHook', 'afterEach']
        ];
    }

    /**
     * @dataProvider hooksProvider
     */
    public function testParsingSimpleTestCaseHasHooks(string $testCaseGetter, string $subNamespace, string $methodName) {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/' . $subNamespace)->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $myTestCase = $testSuite->getTestCaseModels()[0];

        $this->assertCount(1, $myTestCase->$testCaseGetter());
        $this->assertSame('Acme\\DemoSuites\\ImplicitDefaultTestSuite\\' . $subNamespace . '\\MyTestCase', $myTestCase->$testCaseGetter()[0]->getClass());
        $this->assertSame($methodName, $myTestCase->$testCaseGetter()[0]->getMethod());
    }

    public function testParsingCustomAssertionPlugins() {
        $results = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/HasAssertionPlugin');

        $this->assertCount(2, $results->getPluginModels());

        $pluginNames = array_map(fn(PluginModel $pluginModel) => $pluginModel->getPluginClass(), $results->getPluginModels());
        $expected = [MyCustomAssertionPlugin::class, MyOtherCustomAssertionPlugin::class];

        $this->assertEqualsCanonicalizing($expected, $pluginNames);
    }

    public function testParsingDataProvider() {
        $results = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/HasDataProvider');

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $results->getTestSuiteModels()[0];

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $testCaseModel = $testSuite->getTestCaseModels()[0];

        $this->assertSame('Acme\\DemoSuites\\ImplicitDefaultTestSuite\\HasDataProvider\\MyTestCase', $testCaseModel->getTestCaseClass());
        $this->assertCount(1, $testCaseModel->getTestMethodModels());
        $testMethodModel = $testCaseModel->getTestMethodModels()[0];

        $this->assertSame('ensureStringsEqual', $testMethodModel->getMethod());
        $this->assertSame('myDataProvider', $testMethodModel->getDataProvider());
    }

    public function testParsingResultTelemetry() {
        $results = $this->subject->parse($this->acmeSrcDir . '/ImplicitDefaultTestSuite/ExtendedTestCases');

        $this->assertEquals(1, $results->getTestSuiteCount());
        $this->assertEquals(3, $results->getTotalTestCaseCount());
        $this->assertEquals(9, $results->getTotalTestCount());
    }

    private function fetchTestCaseModel(TestSuiteModel $testSuite, string $className) : TestCaseModel {
        foreach ($testSuite->getTestCaseModels() as $testCaseModel) {
            if ($testCaseModel->getTestCaseClass() === $className) {
                return $testCaseModel;
            }
        }
        $this->fail('Expected TestSuite to have TestCase ' . $className);
    }


}
