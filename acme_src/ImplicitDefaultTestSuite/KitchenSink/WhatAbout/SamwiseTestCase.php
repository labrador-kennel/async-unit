<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\WhatAbout;

use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

#[AttachToTestSuite(PotatoTestSuite::class)]
class SamwiseTestCase extends TestCase {

    #[Test]
    public function isBestHobbit() {
        $this->assert()->stringEquals('Samwise', $this->testSuite()->get('bestHobbit'));
    }

}