<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

/**
 * Class DataProvider
 * @package Cspray\Labrador\AsyncUnit\Attribute
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class DataProvider {

    public function __construct(private string $methodName) {}

}