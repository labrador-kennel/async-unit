<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface TestOutput {

    public function write(string $text) : void;

    public function writeln(string $text) : void;

}