<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\AfterAllNonStaticMethod;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class BadTestCase extends TestCase {

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