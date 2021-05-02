<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

interface AfterAllMethodAware {

    public function getClass() : string;

    public function addAfterAllMethod(AfterAllMethodModel $afterAllMethodModel) : void;

}