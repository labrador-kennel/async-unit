<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncTesting\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ProtocolRequiresAttribute {

    public function __construct(
        private string $requiredAttribute
    ) {}

}