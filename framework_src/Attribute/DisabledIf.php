<?php

namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

#[Attribute]
class DisabledIf {

    public function __construct(private string $methodName, private ?string $reason = null) {
    }

}