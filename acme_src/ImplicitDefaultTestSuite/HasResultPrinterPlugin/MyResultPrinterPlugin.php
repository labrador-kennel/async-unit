<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasResultPrinterPlugin;

use Amp\ByteStream\OutputStream;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\ResultPrinterPlugin;
use Cspray\Labrador\AsyncUnit\Event\TestInvokedEvent;
use Cspray\Labrador\AsyncUnit\Events;

class MyResultPrinterPlugin implements ResultPrinterPlugin {

    public function registerEvents(EventEmitter $emitter, OutputStream $output) : void {
        $emitter->on(Events::TEST_INVOKED, function(TestInvokedEvent $event) use($output) {
            yield $output->write($event->getTarget()->getTestCase()::class . "\n");
            yield $output->write($event->getTarget()->getTestMethod() . "\n");
        });
    }

}