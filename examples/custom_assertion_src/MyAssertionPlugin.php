<?php declare(strict_types=1);

namespace Acme\Examples\CustomAssertion;

use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\CustomAssertionPlugin;
use function Amp\call;

class MyAssertionPlugin implements CustomAssertionPlugin {

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : Promise {
        return call(function() use($customAssertionContext) {
            $customAssertionContext->registerAssertion('isAsyncUnitString', fn($actual) => new MyAssertion($actual));
        });
    }

}