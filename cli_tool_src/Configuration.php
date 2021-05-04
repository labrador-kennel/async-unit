<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

interface Configuration {

    /**
     * @return string[]
     */
    public function getTestDirectories() : array;

    /**
     * @return string[]
     */
    public function getPlugins() : array;

}