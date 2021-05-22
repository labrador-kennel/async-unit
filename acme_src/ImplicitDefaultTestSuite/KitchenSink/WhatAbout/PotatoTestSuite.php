<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\WhatAbout;

use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\TestSuite;

class PotatoTestSuite extends TestSuite {

    #[BeforeAll]
    public function setBestHobbit() {
        $this->set('bestHobbit', 'Samwise');
    }


}