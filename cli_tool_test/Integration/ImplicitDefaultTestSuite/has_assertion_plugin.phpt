--TEST--
Ensure single test has appropriate format
--FILE--
<?php

$root = dirname(__DIR__, 3);
$command = "cd $root && php $root/bin/asyncunit run acme_src/ImplicitDefaultTestSuite/HasAssertionPlugin";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
AsyncUnit v%s - %s

Runtime: PHP 8.0.3

.

OK!
Tests: 1, Assertions: 1, Async Assertions: 0

Status: 0
