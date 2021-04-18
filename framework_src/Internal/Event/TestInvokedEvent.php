<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Internal\Event;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Internal\InternalEventNames;
use Cspray\Labrador\AsyncUnit\Internal\Model\InvokedTestCaseTestModel;

/**
 * @internal
 */
class TestInvokedEvent extends StandardEvent implements Event {


    public function __construct(InvokedTestCaseTestModel $target, array $data = []) {
        parent::__construct(InternalEventNames::TEST_INVOKED, $target, $data);
    }

    public function getTarget() : InvokedTestCaseTestModel {
        return parent::getTarget();
    }

}