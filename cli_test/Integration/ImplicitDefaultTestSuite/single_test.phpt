--TEST--
Ensure single test shows appropriate information
--FILE--
<?php

$root = dirname(__DIR__, 3);
$command = "cd $root && php $root/bin/asyncunit run acme_src/ImplicitDefaultTestSuite/SingleTest";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
AsyncUnit v%s - %s

Runtime: PHP 8.0.%d

%s.%s

Time: %d:%f, Memory: %d.%d MB

OK!
Tests: 1, Assertions: 1, Async Assertions: 0

Status: 0
