<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Attribute;


#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class Disabled {

    public function __construct(private ?string $reason = null) {}

}