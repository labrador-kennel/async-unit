<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\Plugin\Plugin;

interface ResultPrinterPlugin extends Plugin {

    public function registerEvents(EventEmitter $emitter, TestOutput $output) : void;

}