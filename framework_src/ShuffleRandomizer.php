<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

class ShuffleRandomizer implements Randomizer {

    public function randomize(array $items) : array {
        shuffle($items);
        return $items;
    }
}