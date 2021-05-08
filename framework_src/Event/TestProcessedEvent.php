<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Model\InvokedTestCaseTestModel;
use Cspray\Labrador\AsyncUnit\TestResult;

final class TestProcessedEvent extends StandardEvent implements Event {

    public function __construct(TestResult $target, array $data = []) {
        parent::__construct(Events::TEST_PROCESSED, $target, $data);
    }

    public function getTarget() : TestResult {
        return parent::getTarget();
    }

}