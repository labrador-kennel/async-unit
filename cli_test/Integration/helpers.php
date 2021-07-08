<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Integration;

function runTests(string $configFile) {
    $root = dirname(__DIR__, 2);
    $configPath = $root . '/' . $configFile;
    $command = "cd $root && php $root/bin/asyncunit run --config $configPath";

    passthru($command, $statusCode);
    echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
}
