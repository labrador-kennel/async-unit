<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface AssertionMessage {

    public function toString() : string;

    public function toNotString() : string;

}