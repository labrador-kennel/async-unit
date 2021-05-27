<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Timeout {

    public function __construct(private int $timeoutInMilliseconds) {}

}