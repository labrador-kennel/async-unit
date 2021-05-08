--TEST--
Ensure single test has appropriate format
--FILE--
<?php

$root = dirname(__DIR__, 3);
$testRoot = $root . '/acme_src/ImplicitDefaultTestSuite/WithAsyncUnitConfig';
$command = "cd $testRoot && php $root/bin/asyncunit";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
AsyncUnit %s - %s

Runtime: PHP 8.0.%d

..

Time: %d:%f, Memory: %d.%d MB

OK!
Tests: 2, Assertions: 2, Async Assertions: 0

Status: 0
