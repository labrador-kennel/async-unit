<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;

final class TestCaseStartedEvent extends StandardEvent implements Event {

    public function __construct(TestCaseModel $target) {
        parent::__construct(Events::TEST_CASE_STARTED, $target);
    }

    public function getTarget() : TestCaseModel {
        return parent::getTarget();
    }

}