<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

class PluginModel {

    public function __construct(private string $pluginClass) {}

    public function getPluginClass() : string {
        return $this->pluginClass;
    }

}