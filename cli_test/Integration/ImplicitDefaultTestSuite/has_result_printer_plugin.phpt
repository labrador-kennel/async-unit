--TEST--
Ensure single test has appropriate format
--FILE--
<?php

$root = dirname(__DIR__, 3);
$command = "cd $root && php $root/bin/asyncunit run acme_src/ImplicitDefaultTestSuite/HasResultPrinterPlugin";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
Acme\DemoSuites\ImplicitDefaultTestSuite\HasResultPrinterPlugin\MyTestCase
checkSomething

Status: 0
