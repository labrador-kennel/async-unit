<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

interface BeforeAllMethodAware {

    public function getClass() : string;

    public function addBeforeAllMethod(BeforeAllMethodModel $beforeAllMethodModel) : void;

}