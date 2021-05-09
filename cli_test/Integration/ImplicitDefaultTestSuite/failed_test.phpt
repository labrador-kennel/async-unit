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

Runtime: PHP 8.0.%d

X

Time: %d:%f, Memory: %d.%d MB

There was 1 failure:

1) Acme\DemoSuites\ImplicitDefaultTestSuite\TestFailedExceptionThrowingTest\MyTestCase::ensureSomethingFails

Test failure message:

Something barfed

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
%s
%s
%s
%s

FAILURES
Tests: 1, Failures: 1, Assertions: 0, Async Assertions: 0

Status: 1