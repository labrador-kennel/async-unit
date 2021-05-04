<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Model;


trait HookAware {

    private array $hooks = [];

    public function getHooks(string $hookType) : array {
        return $this->hooks[$hookType] ?? [];
    }

    public function addHook(HookModel $hook) : void {
        if (!isset($this->hooks[$hook->getType()])) {
            $this->hooks[$hook->getType()] = [];
        }
        $this->hooks[$hook->getType()][] = $hook;
    }

}