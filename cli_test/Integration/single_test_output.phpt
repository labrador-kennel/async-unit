--TEST--
Ensure single passing test shows appropriate output
--FILE--
<?php

use function Cspray\Labrador\AsyncUnitCli\Integration\runTests;

require_once __DIR__ . '/helpers.php';

runTests('/acme_src/ImplicitDefaultTestSuite/SingleTest/async-unit.json');
?>
--EXPECTF--
AsyncUnit v%s - %s

Runtime: PHP 8.%d.%d

%s.%s

Time: %d:%f, Memory: %d.%d MB

OK!
Tests: 1, Assertions: 1, Async Assertions: 0

Status: 0