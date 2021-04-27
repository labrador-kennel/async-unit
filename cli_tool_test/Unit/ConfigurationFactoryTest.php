<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool\Unit;

use Cspray\Labrador\AsyncUnit\CliTool\ConfigurationFactory;
use Cspray\Labrador\AsyncUnit\CliTool\Exception\InvalidConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\Labrador\AsyncUnit\CliTool\ConfigurationFactory
 */
class ConfigurationFactoryTest extends TestCase {

    private ConfigurationFactory $subject;

    public function setUp() : void {
        $this->subject = new ConfigurationFactory();
    }

    public function badSchemaProvider() : array {
        return [
            'empty_object' => [dirname(__DIR__) . '/dummy_configs/empty_object.json'],
            'bad_keys' => [dirname(__DIR__) . '/dummy_configs/bad_keys.json'],
            'good_keys_bad_types' => [dirname(__DIR__) . '/dummy_configs/good_keys_bad_types.json'],
            'test_dirs_empty' => [dirname(__DIR__) . '/dummy_configs/test_dirs_empty.json'],
            'test_dirs_non_string' => [dirname(__DIR__) . '/dummy_configs/test_dirs_non_string.json'],
            'test_dirs_empty_string' => [dirname(__DIR__) . '/dummy_configs/test_dirs_empty_string.json'],
            'good_keys_but_extra' => [dirname(__DIR__) . '/dummy_configs/good_keys_but_extra.json'],
            'plugins_empty' => [dirname(__DIR__) . '/dummy_configs/plugins_empty.json'],
            'plugins_empty_string' => [dirname(__DIR__) . '/dummy_configs/plugins_empty_string.json'],
            'plugins_non_string' => [dirname(__DIR__) . '/dummy_configs/plugins_non_string.json']
        ];
    }

    /**
     * @dataProvider badSchemaProvider
     */
    public function testBadSchemaThrowsException(string $file) {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            'The JSON file at "%s" does not adhere to the JSON Schema https://labrador-kennel.io/dev/async-unit/schema/cli-config.json',
            $file
        ));

        $this->subject->make($file);
    }

    public function testMinimallyValidReturnsCorrectInformation() {
        $configuration = $this->subject->make(dirname(__DIR__) . '/dummy_configs/minimally_valid.json');

        $this->assertSame(['tests'], $configuration->getTestDirectories());
        $this->assertEmpty($configuration->getPlugins());
    }

    public function testHasPluginsReturnsCorrectInformation() {
        $configuration = $this->subject->make(dirname(__DIR__) . '/dummy_configs/has_plugins.json');

        $this->assertSame(['foo'], $configuration->getTestDirectories());
        $this->assertSame(['FooBar'], $configuration->getPlugins());

    }

}