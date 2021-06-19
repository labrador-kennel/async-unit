<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Cspray\Labrador\AsyncUnit\Configuration;
use Cspray\Labrador\AsyncUnitCli\DefaultResultPrinter;

class TestConfiguration implements Configuration {

    private array $testDirectories = [];

    private array $plugins = [];

    private string $resultPrinterClass = DefaultResultPrinter::class;

    public function __construct() {}

    public function setTestDirectories(array $testDirs) : void {
        $this->testDirectories = $testDirs;
    }

    public function getTestDirectories(): array {
        return $this->testDirectories;
    }

    public function setPlugins(array $plugins) : void {
        $this->plugins = $plugins;
    }

    public function getPlugins(): array {
        return $this->plugins;
    }

    public function setResultPrinterClass(string $resultPrinterClass) : void {
        $this->resultPrinterClass = $resultPrinterClass;
    }

    public function getResultPrinterClass(): string {
        return $this->resultPrinterClass;
    }
}