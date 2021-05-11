--TEST--
Ensure an error is shown with default command and no config found
--FILE--
<?php

$root = dirname(__DIR__, 3);
$command = "cd $root && php $root/bin/asyncunit";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
Unable to execute async-unit from a configuration file! Nothing found at "%s/async-unit.json".

Status: 1
