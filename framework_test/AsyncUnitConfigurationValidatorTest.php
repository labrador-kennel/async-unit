<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncUnit\Stub\TestConfiguration;
use Generator;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class AsyncUnitConfigurationValidatorTest extends PHPUnitTestCase {

    private ConfigurationValidator $subject;

    private TestConfiguration $testConfiguration;

    public function setUp(): void {
        parent::setUp();
        $this->subject = new AsyncUnitConfigurationValidator();
        $this->testConfiguration = new TestConfiguration();
    }

    public function testEmptyTestDirectoriesIsInvalid() {
        Loop::run(function() {
            $this->testConfiguration->setTestDirectories([]);
            /** @var ConfigurationValidationResults $results */
            $results = yield $this->subject->validate($this->testConfiguration);

            $this->assertInstanceOf(ConfigurationValidationResults::class, $results);
            $this->assertFalse($results->isValid());
            $this->assertArrayHasKey('testDirectories', $results->getValidationErrors());
            $this->assertSame(
                ['Must provide at least one directory to scan but none were provided.'],
                $results->getValidationErrors()['testDirectories']
            );
        });
    }

    public function testNonDirectoriesIsInvalid() {
        Loop::run(function() {
            $this->testConfiguration->setTestDirectories([
                __DIR__,
                'not a dir',
                dirname(__DIR__),
                'definitely not a dir'
            ]);
            /** @var ConfigurationValidationResults $results */
            $results = yield $this->subject->validate($this->testConfiguration);

            $this->assertInstanceOf(ConfigurationValidationResults::class, $results);
            $this->assertFalse($results->isValid());
            $this->assertArrayHasKey('testDirectories', $results->getValidationErrors());
            $this->assertSame(
                [
                    'A configured directory to scan, "not a dir", is not a directory.',
                    'A configured directory to scan, "definitely not a dir", is not a directory.'
                ],
                $results->getValidationErrors()['testDirectories']
            );
        });
    }

    public function testResultPrinterClassIsNotClass() {
        Loop::run(function() {
            $this->testConfiguration->setResultPrinterClass('Not a class');

            /** @var ConfigurationValidationResults $results */
            $results = yield $this->subject->validate($this->testConfiguration);

            $this->assertInstanceOf(ConfigurationValidationResults::class, $results);
            $this->assertFalse($results->isValid());
            $this->assertArrayHasKey('resultPrinterClass', $results->getValidationErrors());
            $this->assertSame(
                ['The result printer "Not a class" is not a class that can be found. Please ensure this class is configured to be autoloaded through Composer.'],
                $results->getValidationErrors()['resultPrinterClass']
            );
        });
    }

    public function testResultPrinterClassIsNotResultPrinterPlugin() {
        Loop::run(function() {
            $this->testConfiguration->setResultPrinterClass(Generator::class);

            /** @var ConfigurationValidationResults $results */
            $results = yield $this->subject->validate($this->testConfiguration);

            $this->assertInstanceOf(ConfigurationValidationResults::class, $results);
            $this->assertFalse($results->isValid());
            $this->assertArrayHasKey('resultPrinterClass', $results->getValidationErrors());
            $this->assertSame(
                ['The result printer "Generator" is not a ' . ResultPrinterPlugin::class . '. Please ensure your result printer implements this interface.'],
                $results->getValidationErrors()['resultPrinterClass']
            );
        });
    }

}