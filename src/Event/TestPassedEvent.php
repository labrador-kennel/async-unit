<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Event;

use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncTesting\Events;
use Cspray\Labrador\AsyncTesting\TestResult;

class TestPassedEvent extends StandardEvent {

    public function __construct(TestResult $target, array $data = []) {
        parent::__construct(Events::TEST_PASSED_EVENT, $target, $data);
    }

    public function getTarget() : TestResult {
        return parent::getTarget();
    }
}