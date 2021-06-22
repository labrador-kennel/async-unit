<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\File\Driver;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Exception\InvalidConfigurationException;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Uri;
use Opis\JsonSchema\Validator;
use stdClass;
use function Amp\call;
use function Amp\File\filesystem;

final class JsonConfigurationFactory implements ConfigurationFactory {

    private Validator $validator;
    private Schema $schema;
    private Driver $filesystem;

    public function __construct() {
        $this->validator = new Validator();
        $this->validator->resolver()->registerFile(
            'https://labrador-kennel.io/dev/async-unit/schema/cli-config.json',
            dirname(__DIR__) . '/resources/schema/cli-config.json'
        );
        $schema = $this->validator->loader()->loadSchemaById(
            Uri::parse('https://labrador-kennel.io/dev/async-unit/schema/cli-config.json')
        );
        if (is_null($schema)) {
            throw new InvalidConfigurationException('Could not locate the schema for validating CLI configurations');
        }
        $this->schema = $schema;
        $this->filesystem = filesystem();
    }

    public function make(string $configFile) : Promise {
        return call(function() use($configFile) {
            $contents = yield $this->filesystem->get($configFile);
            $configJson = json_decode($contents);
            $results = $this->validator->validate($configJson, $this->schema);
            if ($results->hasError()) {
                $msg = sprintf(
                    'The JSON file at "%s" does not adhere to the JSON Schema https://labrador-kennel.io/dev/async-unit/schema/cli-config.json',
                    $configFile
                );
                throw new InvalidConfigurationException($msg);
            }

            $absoluteTestDirs = [];
            foreach ($configJson->testDirs as $testDir) {
                $absoluteTestDirs[] = realpath($testDir);
            }
            $configJson->testDirs = $absoluteTestDirs;

            return new class($configJson) implements Configuration {

                public function __construct(private stdClass $config) {}

                public function getTestDirectories() : array {
                    return $this->config->testDirs;
                }

                public function getPlugins() : array {
                    return $this->config->plugins ?? [];
                }

                public function getResultPrinter(): string {
                    return $this->config->resultPrinter;
                }

                public function getMockBridge(): ?string {
                    return '';
                }
            };
        });
    }

}