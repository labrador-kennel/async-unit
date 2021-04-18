<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal;

trait LastAssertionCalledTrait {

    private ?string $lastAssertionFile = null;

    private ?int $lastAssertionLine = null;

    public function setLastAssertionFile(string $file) : void {
        $this->lastAssertionFile = $file;
    }

    public function getLastAssertionFile() : ?string {
        return $this->lastAssertionFile;
    }

    public function setLastAssertionLine(int $line) : void {
        $this->lastAssertionLine = $line;
    }

    public function getLastAssertionLine() : int {
        return $this->lastAssertionLine;
    }

}