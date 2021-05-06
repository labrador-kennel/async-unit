--TEST--
Ensure single failing test from bad assertion has appropriate format
--FILE--
<?php

$root = dirname(__DIR__, 3);
$command = "cd $root && php $root/bin/asyncunit run acme_src/ImplicitDefaultTestSuite/FailedAssertion";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
AsyncUnit v%s - %s

Runtime: PHP 8.0.%d

X

There was 1 failure:

1) Acme\DemoSuites\ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::alwaysFails
Failed comparing actual value 'bar' equals 'foo'

%s/acme_src/ImplicitDefaultTestSuite/FailedAssertion/MyTestCase.php:12

FAILURES
Tests: 1, Failures: 1, Assertions: 1, Async Assertions: 0

Status: 1