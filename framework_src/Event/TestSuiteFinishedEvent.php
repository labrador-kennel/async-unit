<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\TestSuite;

class TestSuiteFinishedEvent extends StandardEvent implements Event {

    public function __construct(TestSuiteModel $target) {
        parent::__construct(Events::TEST_SUITE_FINISHED, $target);
    }

    public function getTarget() : TestSuiteModel {
        return parent::getTarget();
    }

}