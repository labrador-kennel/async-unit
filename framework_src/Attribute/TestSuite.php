<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class TestSuite {

    public function __construct(private string $testSuiteClass) {}

}