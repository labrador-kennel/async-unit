<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Event;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncTesting\EventNames;
use Cspray\Labrador\AsyncTesting\Internal\Model\InvokedTestCaseTestModel;

class TestInvokedEvent extends StandardEvent implements Event {

    public function __construct(InvokedTestCaseTestModel $target, array $data = []) {
        parent::__construct(EventNames::TEST_INVOKED, $target, $data);
    }

}