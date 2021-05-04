<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\PreRunSummary;

final class TestProcessingStartedEvent extends StandardEvent {

    public function __construct(PreRunSummary $preRunTestSummary) {
        parent::__construct(Events::TEST_PROCESSING_STARTED, $preRunTestSummary);
    }

    public function getTarget() : PreRunSummary {
        return parent::getTarget();
    }

}