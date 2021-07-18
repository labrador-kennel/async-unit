<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Attribute;

use Attribute;

#[Attribute]
final class BeforeEachTest {

    public function __construct(private int $priority = 0) {}

}