<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Model;


use Cspray\Labrador\AsyncUnit\HookType;

trait HookAware {

    private array $hooks = [];

    /**
     * @param HookType $hookType
     * @return HookModel[]
     */
    public function getHooks(HookType $hookType) : array {
        return $this->hooks[$hookType->toString()] ?? [];
    }

    public function addHook(HookModel $hook) : void {
        if (!isset($this->hooks[$hook->getType()->toString()])) {
            $this->hooks[$hook->getType()->toString()] = [];
        }
        $this->hooks[$hook->getType()->toString()][] = $hook;
    }

}