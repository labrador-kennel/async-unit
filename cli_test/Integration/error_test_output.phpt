--TEST--
Ensure erroring test shows appropriate output
--FILE--
<?php

use function Cspray\Labrador\AsyncUnitCli\Integration\runTests;

require_once __DIR__ . '/helpers.php';

runTests('/acme_src/ImplicitDefaultTestSuite/ExceptionThrowingTest/async-unit.json');
?>
--EXPECTF--
AsyncUnit v%s - %s

Runtime: PHP 8.0.%d

%sE%s

Time: %d:%f, Memory: %d.%d MB

There was 1 error:

1) Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::throwsException
An unexpected exception of type "Exception" with code 0 and message "Test failure" was thrown from #[Test] Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::throwsException

%a

ERRORS
Tests: 1, Errors: 1, Assertions: 0, Async Assertions: 0

Status: 0