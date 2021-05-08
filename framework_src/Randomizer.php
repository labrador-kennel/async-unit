<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface Randomizer {

    public function randomize(array $items) : array;

}