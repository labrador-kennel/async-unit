--TEST--
Ensure single test has appropriate format
--FILE--
<?php

$root = dirname(__DIR__, 3);
$command = "cd $root && php $root/bin/asyncunit run acme_src/ImplicitDefaultTestSuite/SingleTestDisabled";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
AsyncUnit v%s - %s

Runtime: PHP 8.0.%d

D

Time: %d:%f, Memory: %d.%d MB

There was 1 disabled test:

1) Acme\DemoSuites\ImplicitDefaultTestSuite\SingleTestDisabled\MyTestCase::skippedTest

Tests: 1, Disabled Tests: 1, Assertions: 0, Async Assertions: 0

Status: 0
