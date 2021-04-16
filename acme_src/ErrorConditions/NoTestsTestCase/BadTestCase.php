<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\NoTestsTestCase;

use Cspray\Labrador\AsyncTesting\TestCase;

class BadTestCase implements TestCase {

    public function getName() {
        return self::class;
    }

    // We forgot to mark this as a Test
    public function ensureSomethingHappens() {

    }
}