<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\WhatAbout;

use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

#[AttachToTestSuite(PotatoTestSuite::class)]
class BilboTestCase extends TestCase {

    #[Test]
    #[Disabled]
    public function isBestHobbit() {
        throw new \RuntimeException('Bilbo doesn\'t come on this adventure.');
    }

}