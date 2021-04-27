<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\CliTool;

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