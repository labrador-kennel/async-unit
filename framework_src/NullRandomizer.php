<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

final class NullRandomizer implements Randomizer {

    public function randomize(array $items) : array {
        return $items;
    }

}