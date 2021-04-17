<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting;

interface Events {

    const TEST_PASSED_EVENT = 'labrador.asyncTesting.testPassed';

    const TEST_FAILED_EVENT = 'labrador.asyncTesting.testFailed';

}