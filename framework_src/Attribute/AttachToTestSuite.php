<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

/**
 * Class AttachToTestSuite
 * @package Cspray\Labrador\AsyncUnit\Attribute
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AttachToTestSuite {

    public function __construct(private string $testSuiteClass) {}

}