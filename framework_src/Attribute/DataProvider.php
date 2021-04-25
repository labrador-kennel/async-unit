<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class DataProvider {

    public function __construct(private string $methodName) {}

}