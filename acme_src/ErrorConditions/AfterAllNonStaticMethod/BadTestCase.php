<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\AfterAllNonStaticMethod;

use Cspray\Labrador\AsyncTesting\Attribute\AfterAll;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class BadTestCase implements TestCase {

    #[AfterAll]
    public function badAfterAllMustBeStatic() {

    }

    #[Test]
    public function ensureSomething() {

    }

    public function getName() : string {
        return self::class;
    }
}