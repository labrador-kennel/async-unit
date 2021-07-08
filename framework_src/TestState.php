<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

final class TestState implements Enum {

    use EnumTrait;

    public static function Passed() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Failed() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Disabled() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Errored() : self {
        return self::getSingleton(__FUNCTION__);
    }

    static protected function getAllowedValues() : array {
        return ['Passed', 'Failed', 'Disabled', 'Error'];
    }
}