<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\CustomAssertionPlugin;

class FooAssertionPlugin implements CustomAssertionPlugin {

    private ?CustomAssertionContext $context = null;

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : Promise {
        $this->context = $customAssertionContext;
        return new Success();
    }

    public function getCustomAssertionContext() : ?CustomAssertionContext {
        return $this->context;
    }
}