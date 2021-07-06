<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Promise;

interface ConfigurationValidator {

    public function validate(Configuration $configuration) : Promise;

}