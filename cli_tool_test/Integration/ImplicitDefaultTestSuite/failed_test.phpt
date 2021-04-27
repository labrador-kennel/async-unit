--TEST--
Ensure single failing test from bad assertion has appropriate format
--FILE--
<?php

$root = dirname(__DIR__, 3);
$command = "cd $root && php $root/bin/asyncunit run acme_src/ImplicitDefaultTestSuite/TestFailedExceptionThrowingTest";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
AsyncUnit v%s - %s

Runtime: PHP 8.0.3

X

There was 1 failure:

1) Acme\DemoSuites\ImplicitDefaultTestSuite\TestFailedExceptionThrowingTest\MyTestCase::ensureSomethingFails
An unexpected Cspray\Labrador\AsyncUnit\Exception\TestFailedException was thrown in %s/acme_src/ImplicitDefaultTestSuite/TestFailedExceptionThrowingTest/MyTestCase.php on line 13.

"Something barfed"

%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s
%s

FAILURES
Tests: 1, Failures: 1, Assertions: 0, Async Assertions: 0

Status: 1