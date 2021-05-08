<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

final class HookType implements Enum {

    use EnumTrait;

    public static function BeforeAll() {
        return self::getSingleton(__FUNCTION__);
    }

    public static function BeforeEach() {
        return self::getSingleton(__FUNCTION__);
    }

    public static function AfterEach() {
        return self::getSingleton(__FUNCTION__);
    }

    public static function AfterAll() {
        return self::getSingleton(__FUNCTION__);
    }

    public static function BeforeEachTest() {
        return self::getSingleton(__FUNCTION__);
    }

    public static function AfterEachTest() {
        return self::getSingleton(__FUNCTION__);
    }

    static protected function getAllowedValues() : array {
        return [
            'BeforeAll',
            'BeforeEach',
            'AfterEach',
            'AfterAll',
            'BeforeEachTest',
            'AfterEachTest'
        ];
    }
}