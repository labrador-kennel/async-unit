<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BeforeAllNonStaticMethod;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class BadTestCase extends TestCase {

    #[BeforeAll]
    public function badBeforeAllMustBeStatic() {

    }

    #[Test]
    public function ensureSomething() {

    }

    public function getName() {
        return self::class;
    }
}