<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Statistics\ProcessedAggregateSummary;

final class ProcessingFinishedEvent extends StandardEvent {

    public function __construct(ProcessedAggregateSummary $summary) {
        parent::__construct(Events::PROCESSING_FINISHED, $summary);
    }

    public function getTarget() : ProcessedAggregateSummary {
        return parent::getTarget();
    }

}