<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\Plugin\Plugin;

interface CustomAssertionPlugin extends Plugin {

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : Promise;

}