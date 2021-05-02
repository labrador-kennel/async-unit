<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\PostRunSummary;

final class TestProcessingFinishedEvent extends StandardEvent {

    public function __construct(PostRunSummary $summary) {
        parent::__construct(Events::TEST_PROCESSING_FINISHED_EVENT, $summary);
    }

    public function getTarget() : PostRunSummary {
        return parent::getTarget();
    }

}