<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Cspray\Labrador\AsyncUnit\Exception\InvalidConfigurationException;
use Cspray\Labrador\AsyncUnit\MockBridge\MockeryMockBridge;
use Cspray\Labrador\AsyncUnitCli\DefaultResultPrinter;
use PHPUnit\Framework\TestCase;

class JsonConfigurationFactoryTest extends TestCase {

    private JsonConfigurationFactory $subject;

    public function setUp() : void {
        $this->subject = new JsonConfigurationFactory();
    }

    public function badSchemaProvider() : array {
        return [
            'empty_object' => [__DIR__ . '/Resources/dummy_configs/empty_object.json'],
            'bad_keys' => [__DIR__ . '/Resources/dummy_configs/bad_keys.json'],
            'good_keys_bad_types' => [__DIR__ . '/Resources/dummy_configs/good_keys_bad_types.json'],
            'test_dirs_empty' => [__DIR__ . '/Resources/dummy_configs/test_dirs_empty.json'],
            'test_dirs_non_string' => [__DIR__ . '/Resources/dummy_configs/test_dirs_non_string.json'],
            'test_dirs_empty_string' => [__DIR__ . '/Resources/dummy_configs/test_dirs_empty_string.json'],
            'good_keys_but_extra' => [__DIR__ . '/Resources/dummy_configs/good_keys_but_extra.json'],
            'plugins_empty' => [__DIR__ . '/Resources/dummy_configs/plugins_empty.json'],
            'plugins_empty_string' => [__DIR__ . '/Resources/dummy_configs/plugins_empty_string.json'],
            'plugins_non_string' => [__DIR__ . '/Resources/dummy_configs/plugins_non_string.json'],
            'result_printer_null' => [__DIR__ . '/Resources/dummy_configs/result_printer_null.json'],
            'result_printer_empty' => [__DIR__ . '/Resources/dummy_configs/result_printer_empty.json'],
            'mock_bridge_empty_string' => [__DIR__ . '/Resources/dummy_configs/mock_bridge_empty_string.json']
        ];
    }

    /**
     * @dataProvider badSchemaProvider
     */
    public function testBadSchemaThrowsException(string $file) {
        Loop::run(function() use($file) {
            $this->expectException(InvalidConfigurationException::class);
            $this->expectExceptionMessage(sprintf(
                'The JSON file at "%s" does not adhere to the JSON Schema https://labrador-kennel.io/dev/async-unit/schema/cli-config.json',
                $file
            ));

            yield $this->subject->make($file);
        });
    }

    public function testMinimallyValidReturnsCorrectInformation() {
        Loop::run(function() {
            /** @var Configuration $configuration */
            $configuration = yield $this->subject->make(__DIR__ . '/Resources/dummy_configs/minimally_valid.json');

            $this->assertSame([getcwd()], $configuration->getTestDirectories());
            $this->assertSame(DefaultResultPrinter::class, $configuration->getResultPrinter());
            $this->assertEmpty($configuration->getPlugins());
        });
    }

    public function testHasPluginsReturnsCorrectInformation() {
        Loop::run(function() {
            $configuration = yield $this->subject->make(__DIR__ . '/Resources/dummy_configs/has_plugins.json');

            $this->assertSame([getcwd()], $configuration->getTestDirectories());
            $this->assertSame(['FooBar'], $configuration->getPlugins());
        });
    }

    public function testHasMockBridgeReturnsCorrectInformation() {
        Loop::run(function() {
            $configuration = yield $this->subject->make(__DIR__ . '/Resources/dummy_configs/has_mock_bridge.json');

            $this->assertSame([getcwd()], $configuration->getTestDirectories());
            $this->assertSame(MockeryMockBridge::class, $configuration->getMockBridge());
        });
    }

}