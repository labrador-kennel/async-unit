<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin;

use Amp\Promise;
use Amp\Success;
use Countable;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsTrue;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\CustomAssertionPlugin;
use Stringable;

class MyOtherCustomAssertionPlugin implements Countable, Stringable, CustomAssertionPlugin {

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : Promise {
        $customAssertionContext->registerAssertion('myOtherCustomAssertion', function() {
            return new AssertIsTrue(true);
        });
        return new Success();
    }

    public function __toString() {
        return '';
    }

    public function count() {
        return 0;
    }
}