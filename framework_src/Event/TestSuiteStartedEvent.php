<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\Statistics\TestSuiteSummary;
use Cspray\Labrador\AsyncUnit\TestSuite;

final class TestSuiteStartedEvent extends StandardEvent implements Event {

    public function __construct(TestSuiteSummary $testSuiteSummary) {
        parent::__construct(Events::TEST_SUITE_STARTED, $testSuiteSummary);
    }

    public function getTarget() : TestSuiteSummary {
        return parent::getTarget();
    }

}