<?php

namespace Cspray\Labrador\AsyncUnit\Model;

interface DisabledDeterminator {

    public function isDisabled() : bool;

    public function getReason() : ?string;

}