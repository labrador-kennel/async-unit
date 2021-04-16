<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BeforeAllNonStaticMethod;

use Cspray\Labrador\AsyncTesting\Attribute\BeforeAll;
use Cspray\Labrador\AsyncTesting\Attribute\Test;
use Cspray\Labrador\AsyncTesting\TestCase;

class BadTestCase implements TestCase {

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