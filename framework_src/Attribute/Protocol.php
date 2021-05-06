<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

/**
 * Class Protocol
 * @package Cspray\Labrador\AsyncUnit\Attribute
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Protocol {

    public function __construct(private array $validTypes) {}

}