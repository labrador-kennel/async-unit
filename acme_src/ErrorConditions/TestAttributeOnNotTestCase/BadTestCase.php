<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\TestAttributeOnNotTestCase;

use Cspray\Labrador\AsyncTesting\Attribute\Test;

class BadTestCase {

    // We forgot to implement TestCase

    #[Test]
    public function ensureSomething() {

    }

}