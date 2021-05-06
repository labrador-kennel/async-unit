<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasResultPrinterPlugin;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\ResultPrinterPlugin;
use Cspray\Labrador\AsyncUnit\TestOutput;
use Cspray\Labrador\AsyncUnit\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncUnit\Events;

class MyResultPrinterPlugin implements ResultPrinterPlugin {

    public function registerEvents(EventEmitter $emitter, TestOutput $output) : void {
        $emitter->on(Events::TEST_INVOKED, function(TestInvokedEvent $event) use($output) {
            $output->writeln($event->getTarget()->getTestCase()::class);
            $output->writeln($event->getTarget()->getTestMethod());
        });
    }

}