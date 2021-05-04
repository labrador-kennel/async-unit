<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

interface Events {

    const TEST_PROCESSING_STARTED = 'labrador.asyncUnit.testProcessingStarted';

    const TEST_INVOKED = 'labrador.asyncUnit.testInvoked';

    const TEST_PASSED = 'labrador.asyncUnit.testPassed';

    const TEST_FAILED = 'labrador.asyncUnit.testFailed';

    const TEST_PROCESSING_FINISHED = 'labrador.asyncUnit.testProcessingFinished';

}