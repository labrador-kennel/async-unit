<?php declare(strict_types=1);

namespace Acme\Examples\CustomAssertion;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class TestCaseUsesMyAssertion extends TestCase {

    #[Test]
    public function ensureAsyncUnitStringEquals() {
        $this->assert()->isAsyncUnitString('AsyncUnit');
    }

    #[Test]
    public function ensurePhpUnitStringNotEquals() {
        $this->assert()->not()->isAsyncUnitString('PHPUnit');
    }

}