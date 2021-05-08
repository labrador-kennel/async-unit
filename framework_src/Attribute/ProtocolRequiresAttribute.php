<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

/**
 * Class ProtocolRequiresAttribute
 * @package Cspray\Labrador\AsyncUnit\Attribute
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class ProtocolRequiresAttribute {

    public function __construct(
        private string $requiredAttribute
    ) {}

}