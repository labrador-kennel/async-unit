<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit;


use Amp\Promise;

interface ConfigurationFactory {

    /**
     * @param string $path
     * @return Promise<Configuration>
     */
    public function make(string $path) : Promise;

}