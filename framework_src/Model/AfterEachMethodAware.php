<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

interface AfterEachMethodAware {

    public function getClass() : string;

    public function addAfterEachMethod(HookMethodModel $model) : void;

}