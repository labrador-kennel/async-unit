<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class ProtocolRequiresAttribute {

    public function __construct(
        private string $requiredAttribute
    ) {}

}