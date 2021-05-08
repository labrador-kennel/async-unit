<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\TestResult;

final class TestDisabledEvent extends StandardEvent implements Event {

    public function __construct(TestResult $testResult) {
        parent::__construct(Events::TEST_DISABLED, $testResult);
    }

    public function getTarget() : TestResult {
        return parent::getTarget();
    }

}