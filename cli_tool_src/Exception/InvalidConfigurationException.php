<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool\Exception;

use Opis\JsonSchema\ValidationResult;
use Throwable;

class InvalidConfigurationException extends Exception {

    public function getSchemaValidationResult() : ?ValidationResult {

    }

}