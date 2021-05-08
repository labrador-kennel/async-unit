<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Exception;

use Opis\JsonSchema\ValidationResult;
use Throwable;

class InvalidConfigurationException extends Exception {

    public function getSchemaValidationResult() : ?ValidationResult {

    }

}