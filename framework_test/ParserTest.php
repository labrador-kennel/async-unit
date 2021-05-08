<?php

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class ParserTest extends PHPUnitTestCase {

    use AsyncUnitAssertions;
    use UsesAcmeSrc;

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
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\TestAttributeOnNotTestCase\\BadTestCase". The method "ensureSomething" is annotated with #[Test] but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/TestAttributeOnNotTestCase');
    }

    public function testErrorConditionsBeforeAllAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\BeforeAllAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is annotated with #[BeforeAll] but this class does not extend "' . TestSuite::class . '" or "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/BeforeAllAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsAfterAllAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\AfterAllAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is annotated with #[AfterAll] but this class does not extend "' . TestSuite::class . '" or "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/AfterAllAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsAfterEachAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\AfterEachAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is annotated with #[AfterEach] but this class does not extend "' . TestSuite::class . '" or "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/AfterEachAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsBeforeEachAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\BeforeEachAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is annotated with #[BeforeEach] but this class does not extend "' . TestSuite::class . '" or "' . TestCase::class . '".');
        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/BeforeEachAttributeOnNotTestCaseOrTestSuite');
    }

    public function testDefaultTestSuiteName() {
        $testSuites = $this->subject->parse($this->implicitDefaultTestSuitePath('SingleTest'))->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertSame(ImplicitTestSuite::class, $testSuite->getClass());
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteSingleTest() {
        $testSuites = $this->subject->parse($this->implicitDefaultTestSuitePath('SingleTest'))->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $expectedTestCase = ImplicitDefaultTestSuite\SingleTest\MyTestCase::class;
        $this->assertCount(1, $testSuite->getTestCaseModels());
        $this->assertTestCaseClassBelongsToTestSuite($expectedTestCase, $testSuite);

        $testCaseModel = $this->fetchTestCaseModel($testSuite, $expectedTestCase);

        $this->assertCount(1, $testCaseModel->getTestMethodModels());
        $this->assertTestMethodBelongsToTestCase($expectedTestCase . '::ensureSomethingHappens', $testCaseModel);
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteMultipleTest() {
        $testSuites = $this->subject->parse($this->implicitDefaultTestSuitePath('MultipleTest'))->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];
        $expectedTestCase = ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class;
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
        $testSuites = $this->subject->parse($this->implicitDefaultTestSuitePath('HasNotTestCaseObject'))->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $expectedTestCase = ImplicitDefaultTestSuite\HasNotTestCaseObject\MyTestCase::class;
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
        $testSuites = $this->subject->parse($this->implicitDefaultTestSuitePath('MultipleTestCase'))->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $barTestCaseClass = ImplicitDefaultTestSuite\MultipleTestCase\BarTestCase::class;
        $bazTestCaseClass = ImplicitDefaultTestSuite\MultipleTestCase\BazTestCase::class;
        $fooTestCaseClass = ImplicitDefaultTestSuite\MultipleTestCase\FooTestCase::class;

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
        $testSuites = $this->subject->parse($this->implicitDefaultTestSuitePath('ExtendedTestCases'))->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $firstTestCaseClass = ImplicitDefaultTestSuite\ExtendedTestCases\FirstTestCase::class;
        $thirdTestCaseClass = ImplicitDefaultTestSuite\ExtendedTestCases\ThirdTestCase::class;
        $fifthTestCaseClass = ImplicitDefaultTestSuite\ExtendedTestCases\FifthTestCase::class;

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
            [HookType::BeforeAll(), 'HasSingleBeforeAllHook', 'beforeAll'],
            [HookType::BeforeEach(), 'HasSingleBeforeEachHook', 'beforeEach'],
            [HookType::AfterAll(), 'HasSingleAfterAllHook', 'afterAll'],
            [HookType::AfterEach(), 'HasSingleAfterEachHook', 'afterEach']
        ];
    }

    /**
     * @dataProvider hooksProvider
     */
    public function testParsingSimpleTestCaseHasHooks(HookType $hookType, string $subNamespace, string $methodName) {
        $testSuites = $this->subject->parse($this->implicitDefaultTestSuitePath($subNamespace))->getTestSuiteModels();

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $myTestCase = $testSuite->getTestCaseModels()[0];

        $this->assertCount(1, $myTestCase->getHooks($hookType));
        $this->assertSame('Acme\\DemoSuites\\ImplicitDefaultTestSuite\\' . $subNamespace . '\\MyTestCase', $myTestCase->getHooks($hookType)[0]->getClass());
        $this->assertSame($methodName, $myTestCase->getHooks($hookType)[0]->getMethod());
    }

    public function testParsingCustomAssertionPlugins() {
        $results = $this->subject->parse($this->implicitDefaultTestSuitePath('HasAssertionPlugin'));

        $this->assertCount(2, $results->getPluginModels());

        $pluginNames = array_map(fn(PluginModel $pluginModel) => $pluginModel->getPluginClass(), $results->getPluginModels());
        $expected = [ImplicitDefaultTestSuite\HasAssertionPlugin\MyCustomAssertionPlugin::class, ImplicitDefaultTestSuite\HasAssertionPlugin\MyOtherCustomAssertionPlugin::class];

        $this->assertEqualsCanonicalizing($expected, $pluginNames);
    }

    public function testParsingDataProvider() {
        $results = $this->subject->parse($this->implicitDefaultTestSuitePath('HasDataProvider'));

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $results->getTestSuiteModels()[0];

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $testCaseModel = $testSuite->getTestCaseModels()[0];

        $this->assertSame(ImplicitDefaultTestSuite\HasDataProvider\MyTestCase::class, $testCaseModel->getClass());
        $this->assertCount(1, $testCaseModel->getTestMethodModels());
        $testMethodModel = $testCaseModel->getTestMethodModels()[0];

        $this->assertSame('ensureStringsEqual', $testMethodModel->getMethod());
        $this->assertSame('myDataProvider', $testMethodModel->getDataProvider());
    }

    public function testExplicitTestSuiteAnnotatedDefaultTestSuite() {
        $results = $this->subject->parse($this->explicitTestSuitePath('AnnotatedDefaultTestSuite'));

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $results->getTestSuiteModels()[0];

        $this->assertSame(ExplicitTestSuite\AnnotatedDefaultTestSuite\MyTestSuite::class, $testSuite->getClass());
        $this->assertTestCaseClassBelongsToTestSuite(ExplicitTestSuite\AnnotatedDefaultTestSuite\MyTestCase::class, $testSuite);
    }

    public function testExplicitTestSuiteTestCaseDefinesTestSuite() {
        $results = $this->subject->parse($this->explicitTestSuitePath('TestCaseDefinesTestSuite'));

        $this->assertCount(2, $results->getTestSuiteModels());

        $firstTestSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class);
        $this->assertCount(1, $firstTestSuite->getTestCaseModels());

        $this->assertTestCaseClassBelongsToTestSuite(ExplicitTestSuite\TestCaseDefinesTestSuite\FirstTestCase::class, $firstTestSuite);

        $secondTestSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class);
        $this->assertCount(2, $secondTestSuite->getTestCaseModels());

        $this->assertTestCaseClassBelongsToTestSuite(ExplicitTestSuite\TestCaseDefinesTestSuite\SecondTestCase::class, $secondTestSuite);
        $this->assertTestCaseClassBelongsToTestSuite(ExplicitTestSuite\TestCaseDefinesTestSuite\ThirdTestCase::class, $secondTestSuite);
    }

    public function testExplicitTestSuiteTestCaseDefinesAndTestCaseDefaultTestSuite() {
        $results = $this->subject->parse($this->explicitTestSuitePath('TestCaseDefinedAndImplicitDefaultTestSuite'));

        $this->assertCount(2, $results->getTestSuiteModels());

        $defaultTestSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ImplicitTestSuite::class);
        $this->assertCount(1, $defaultTestSuite->getTestCaseModels());
        $this->assertTestCaseClassBelongsToTestSuite(ExplicitTestSuite\TestCaseDefinedAndImplicitDefaultTestSuite\FirstTestCase::class, $defaultTestSuite);

        $myTestSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ExplicitTestSuite\TestCaseDefinedAndImplicitDefaultTestSuite\MyTestSuite::class);
        $this->assertCount(1, $myTestSuite->getTestCaseModels());
        $this->assertTestCaseClassBelongsToTestSuite(ExplicitTestSuite\TestCaseDefinedAndImplicitDefaultTestSuite\SecondTestCase::class, $myTestSuite);
    }

    public function testImplicitDefaultTestSuitePathExtendedTestCases() {
        $results = $this->subject->parse($this->implicitDefaultTestSuitePath('ExtendedTestCases'));

        $this->assertEquals(1, $results->getTestSuiteCount());
        $this->assertEquals(3, $results->getTotalTestCaseCount());
        $this->assertEquals(9, $results->getTotalTestCount());
    }

    public function testImplicitDefaultTestSuitePathHasResultPrinterPlugin() {
        $results = $this->subject->parse($this->implicitDefaultTestSuitePath('HasResultPrinterPlugin'));

        $this->assertCount(1, $results->getPluginModels());
        $pluginModel = $results->getPluginModels()[0];
        $this->assertSame(ImplicitDefaultTestSuite\HasResultPrinterPlugin\MyResultPrinterPlugin::class, $pluginModel->getPluginClass());
    }

    public function testImplicitDefaultTestSuitePathTestDisabled() {
        $results = $this->subject->parse($this->implicitDefaultTestSuitePath('TestDisabled'));

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ImplicitTestSuite::class);
        $testCase = $this->fetchTestCaseModel($testSuite, ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class);

        $this->assertTestMethodBelongsToTestCase(ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class . '::checkSomething', $testCase);
        $this->assertTestMethodBelongsToTestCase(ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class . '::skippedTest', $testCase);

        $checkSomething = $this->fetchTestModel($testCase, 'checkSomething');
        $skippedTest = $this->fetchTestModel($testCase, 'skippedTest');

        $this->assertFalse($checkSomething->isDisabled());
        $this->assertTrue($skippedTest->isDisabled());
        $this->assertNull($skippedTest->getDisabledReason());
    }

    public function testImplicitDefaultTestSuitePathTestCaseDisabled() {
        $results = $this->subject->parse($this->implicitDefaultTestSuitePath('TestCaseDisabled'));

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ImplicitTestSuite::class);
        $testCase = $this->fetchTestCaseModel($testSuite, ImplicitDefaultTestSuite\TestCaseDisabled\MyTestCase::class);

        $this->assertTrue($testCase->isDisabled());

        $this->assertTestMethodBelongsToTestCase(ImplicitDefaultTestSuite\TestCaseDisabled\MyTestCase::class . '::skippedOne', $testCase);
        $this->assertTestMethodBelongsToTestCase(ImplicitDefaultTestSuite\TestCaseDisabled\MyTestCase::class . '::skippedTwo', $testCase);
        $this->assertTestMethodBelongsToTestCase(ImplicitDefaultTestSuite\TestCaseDisabled\MyTestCase::class . '::skippedThree', $testCase);

        $one = $this->fetchTestModel($testCase, 'skippedOne');
        $two = $this->fetchTestModel($testCase, 'skippedTwo');
        $three = $this->fetchTestModel($testCase, 'skippedThree');

        $this->assertTrue($one->isDisabled());
        $this->assertNull($one->getDisabledReason());
        $this->assertTrue($two->isDisabled());
        $this->assertNull($two->getDisabledReason());
        $this->assertTrue($three->isDisabled());
        $this->assertNull($three->getDisabledReason());
    }

    public function testExplicitTestSuiteTestSuiteDisabled() {
        $results = $this->subject->parse($this->explicitTestSuitePath('TestSuiteDisabled'));

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class);
        $this->assertTrue($testSuite->isDisabled());

        $this->assertCount(2, $testSuite->getTestCaseModels());
        $firstTestCase = $this->fetchTestCaseModel($testSuite, ExplicitTestSuite\TestSuiteDisabled\FirstTestCase::class);
        $secondTestCase = $this->fetchTestCaseModel($testSuite, ExplicitTestSuite\TestSuiteDisabled\SecondTestCase::class);

        $this->assertTrue($firstTestCase->isDisabled());
        $this->assertTrue($secondTestCase->isDisabled());

        $this->assertTestMethodBelongsToTestCase(ExplicitTestSuite\TestSuiteDisabled\FirstTestCase::class . '::testOne', $firstTestCase);
        $this->assertTestMethodBelongsToTestCase(ExplicitTestSuite\TestSuiteDisabled\FirstTestCase::class . '::testTwo', $firstTestCase);
        $this->assertTestMethodBelongsToTestCase(ExplicitTestSuite\TestSuiteDisabled\SecondTestCase::class . '::testOne', $secondTestCase);

        $one = $this->fetchTestModel($firstTestCase, 'testOne');
        $two = $this->fetchTestModel($firstTestCase, 'testTwo');
        $three = $this->fetchTestModel($firstTestCase, 'testOne');

        $this->assertTrue($one->isDisabled());
        $this->assertNull($one->getDisabledReason());
        $this->assertTrue($two->isDisabled());
        $this->assertNull($two->getDisabledReason());
        $this->assertTrue($three->isDisabled());
        $this->assertNull($three->getDisabledReason());
    }

    public function testImplicitDefaultTestSuiteTestDisabledCustomMessage() {
        $results = $this->subject->parse($this->implicitDefaultTestSuitePath('TestDisabledCustomMessage'));

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ImplicitTestSuite::class);
        $this->assertFalse($testSuite->isDisabled());

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $testCaseModel = $this->fetchTestCaseModel($testSuite, ImplicitDefaultTestSuite\TestDisabledCustomMessage\MyTestCase::class);

        $this->assertTestMethodBelongsToTestCase(ImplicitDefaultTestSuite\TestDisabledCustomMessage\MyTestCase::class . '::testOne', $testCaseModel);

        $testModel = $this->fetchTestModel($testCaseModel, 'testOne');

        $this->assertTrue($testModel->isDisabled());
        $this->assertSame('Not sure what we should do here yet', $testModel->getDisabledReason());
    }

    public function testImplicitDefaultTestSuiteTestCaseDisabledCustomMessage() {
        $results = $this->subject->parse($this->implicitDefaultTestSuitePath('TestCaseDisabledCustomMessage'));

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ImplicitTestSuite::class);
        $this->assertFalse($testSuite->isDisabled());

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $testCaseModel = $this->fetchTestCaseModel($testSuite, ImplicitDefaultTestSuite\TestCaseDisabledCustomMessage\MyTestCase::class);

        $this->assertTrue($testCaseModel->isDisabled());
        $this->assertSame('The TestCase is disabled', $testCaseModel->getDisabledReason());
    }

    public function testExplicitTestSuiteTestSuiteDisabledCustomMessage() {
        $results = $this->subject->parse($this->explicitTestSuitePath('TestSuiteDisabledCustomMessage'));

        $this->assertCount(1, $results->getTestSuiteModels());
        $testSuite = $this->fetchTestSuiteModel($results->getTestSuiteModels(), ExplicitTestSuite\TestSuiteDisabledCustomMessage\MyTestSuite::class);
        $this->assertTrue($testSuite->isDisabled());
        $this->assertSame('The AttachToTestSuite is disabled', $testSuite->getDisabledReason());
    }

    /**
     * @param TestSuiteModel[] $testSuites
     * @param string $testSuiteClassName
     * @return TestSuiteModel
     */
    private function fetchTestSuiteModel(array $testSuites, string $testSuiteClassName) : TestSuiteModel {
        foreach ($testSuites as $testSuite) {
            if ($testSuite->getClass() === $testSuiteClassName) {
                return $testSuite;
            }
        }
        $this->fail('Expected the set of TestSuites to have a class matching . ' . $testSuiteClassName);
    }

    private function fetchTestCaseModel(TestSuiteModel $testSuite, string $className) : TestCaseModel {
        foreach ($testSuite->getTestCaseModels() as $testCaseModel) {
            if ($testCaseModel->getClass() === $className) {
                return $testCaseModel;
            }
        }
        $this->fail('Expected AttachToTestSuite to have TestCase ' . $className);
    }

    private function fetchTestModel(TestCaseModel $model, string $methodName) : TestModel {
        foreach ($model->getTestMethodModels() as $testMethodModel) {
            if ($testMethodModel->getMethod() === $methodName) {
                return $testMethodModel;
            }
        }
        $this->fail('Expected TestCase to have test ' . $methodName);
    }

}
