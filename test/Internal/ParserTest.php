<?php

namespace Cspray\Labrador\AsyncTesting\Internal;

use Cspray\Labrador\AsyncTesting\Exception\TestCompilationException;
use Cspray\Labrador\AsyncTesting\Internal\Model\TestCaseModel;
use Cspray\Labrador\AsyncTesting\Internal\Model\TestMethodModel;
use Cspray\Labrador\AsyncTesting\TestCase;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @covers \Cspray\Labrador\AsyncTesting\Internal\Parser
 */
class ParserTest extends PHPUnitTestCase {

    private string $acmeSrcDir;
    private Parser $subject;

    public function setUp() : void {
        $this->acmeSrcDir = dirname(__DIR__, 2) . '/acme_src';
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
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\TestAttributeOnNotTestCase\\BadTestCase". The method "ensureSomething" is marked as #[Test] but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/TestAttributeOnNotTestCase');
    }

    public function testErrorConditionsBeforeAllAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\BeforeAllAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is marked as #[BeforeAll] but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/BeforeAllAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsAfterAllAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\AfterAllAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is marked as #[AfterAll] but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/AfterAllAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsAfterEachAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\AfterEachAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is marked as #[AfterEach] but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/AfterEachAttributeOnNotTestCaseOrTestSuite');
    }

    public function testErrorConditionsBeforeEachAttributeOnNotTestCaseOrTestSuite() {
        $this->expectException(TestCompilationException::class);
        $this->expectExceptionMessage('Failure compiling "Acme\\DemoSuites\\ErrorConditions\\BeforeEachAttributeOnNotTestCaseOrTestSuite\\BadTestCase". The method "ensureSomething" is marked as #[BeforeEach] but this class does not extend "' . TestCase::class . '".');

        $this->subject->parse($this->acmeSrcDir . '/ErrorConditions/BeforeEachAttributeOnNotTestCaseOrTestSuite');
    }

    public function testDefaultTestSuiteName() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/SingleTest');

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertSame('Default TestSuite', $testSuite->getName());
    }


    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteSingleTest() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/SingleTest');

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $this->assertInstanceOf(TestCaseModel::class, $testSuite->getTestCaseModels()[0]);
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\SingleTest\\MyTestCase', $testSuite->getTestCaseModels()[0]->getTestCaseClass());

        $this->assertCount(1, $testSuite->getTestCaseModels()[0]->getTestMethodModels());
        $this->assertInstanceOf(TestMethodModel::class, $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]);
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\SingleTest\\MyTestCase', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]->getClass());
        $this->assertSame('ensureSomethingHappens', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]->getMethod());
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteMultipleTest() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/MultipleTest');

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $this->assertInstanceOf(TestCaseModel::class, $testSuite->getTestCaseModels()[0]);
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTest\\MyTestCase', $testSuite->getTestCaseModels()[0]->getTestCaseClass());

        $this->assertCount(3, $testSuite->getTestCaseModels()[0]->getTestMethodModels());

        $this->assertInstanceOf(TestMethodModel::class, $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]);
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTest\\MyTestCase', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]->getClass());
        $this->assertSame('ensureSomethingHappens', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]->getMethod());

        $this->assertInstanceOf(TestMethodModel::class, $testSuite->getTestCaseModels()[0]->getTestMethodModels()[1]);
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTest\\MyTestCase', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[1]->getClass());
        $this->assertSame('ensureSomethingHappensTwice', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[1]->getMethod());

        $this->assertInstanceOf(TestMethodModel::class, $testSuite->getTestCaseModels()[0]->getTestMethodModels()[2]);
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTest\\MyTestCase', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[2]->getClass());
        $this->assertSame('ensureSomethingHappensThreeTimes', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[2]->getMethod());
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteHasNotTestCaseObject() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/HasNotTestCaseObject');

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $this->assertInstanceOf(TestCaseModel::class, $testSuite->getTestCaseModels()[0]);
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\HasNotTestCaseObject\\MyTestCase', $testSuite->getTestCaseModels()[0]->getTestCaseClass());

        $this->assertCount(1, $testSuite->getTestCaseModels()[0]->getTestMethodModels());
        $this->assertInstanceOf(TestMethodModel::class, $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]);
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\HasNotTestCaseObject\\MyTestCase', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]->getClass());
        $this->assertSame('ensureSomethingHappens', $testSuite->getTestCaseModels()[0]->getTestMethodModels()[0]->getMethod());
    }

    public function testParsingSimpleTestCaseImplicitDefaultTestSuiteMultipleTestCase() {
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/MultipleTestCase');

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertCount(3, $testSuite->getTestCaseModels());
        $getTestCase = function(string $className) use($testSuite) {
            foreach ($testSuite->getTestCaseModels() as $testCaseModel) {
                if ($testCaseModel->getTestCaseClass() === $className) {
                    return $testCaseModel;
                }
            }
            return null;
        };

        $barTestCase = $getTestCase('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTestCase\\BarTestCase');
        $bazTestCase = $getTestCase('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTestCase\\BazTestCase');
        $fooTestCase = $getTestCase('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTestCase\\FooTestCase');

        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTestCase\\BarTestCase', $barTestCase->getTestCaseClass());
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTestCase\\BazTestCase', $bazTestCase->getTestCaseClass());
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\MultipleTestCase\\FooTestCase', $fooTestCase->getTestCaseClass());

        $this->assertCount(1, $barTestCase->getTestMethodModels());
        $this->assertCount(1, $bazTestCase->getTestMethodModels());
        $this->assertCount(2, $fooTestCase->getTestMethodModels());
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
        $testSuites = $this->subject->parse($this->acmeSrcDir . '/SimpleTestCase/ImplicitDefaultTestSuite/' . $subNamespace);

        $this->assertCount(1, $testSuites);
        $testSuite = $testSuites[0];

        $this->assertCount(1, $testSuite->getTestCaseModels());
        $myTestCase = $testSuite->getTestCaseModels()[0];

        $this->assertCount(1, $myTestCase->$testCaseGetter());
        $this->assertSame('Acme\\DemoSuites\\SimpleTestCase\\ImplicitDefaultTestSuite\\' . $subNamespace . '\\MyTestCase', $myTestCase->$testCaseGetter()[0]->getClass());
        $this->assertSame($methodName, $myTestCase->$testCaseGetter()[0]->getMethod());
    }


}
