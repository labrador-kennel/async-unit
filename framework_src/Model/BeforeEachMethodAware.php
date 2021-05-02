<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

interface BeforeEachMethodAware {

    public function getClass() : string;

    public function addBeforeEachMethod(BeforeEachMethodModel $beforeEachMethodModel) : void;

}