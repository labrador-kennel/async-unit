<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface Events {

    const TEST_PASSED_EVENT = 'labrador.asyncUnit.testPassed';

    const TEST_FAILED_EVENT = 'labrador.asyncUnit.testFailed';

    const TEST_PROCESSING_FINISHED_EVENT = 'labrador.asyncUnit.testProcessingFinished';

}