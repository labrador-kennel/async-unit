<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Statistics\ProcessedTestSuiteSummary;

final class TestSuiteFinishedEvent extends StandardEvent implements Event {

    public function __construct(ProcessedTestSuiteSummary $target) {
        parent::__construct(Events::TEST_SUITE_FINISHED, $target);
    }

    public function getTarget() : ProcessedTestSuiteSummary {
        return parent::getTarget();
    }

}