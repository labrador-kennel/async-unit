<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Exception;

use Opis\JsonSchema\ValidationResult;

class InvalidConfigurationException extends Exception {

    public function getSchemaValidationResult() : ?ValidationResult {

    }

}