<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once 'ITIVarTypeHandler.php';

// Type Handler for type 0x1D: ExactComplexRadical
class TH_0x1D implements ITIVarTypeHandler
{
    /* Hardcoded to keep PHP 5.5 compatibilty */
    const dataByteCount = 18; // 2 * TH_0x1C::dataByteCount;

    public static function makeDataFromString($str = '', array $options = [])
    {
        throw new \BadMethodCallException('Unimplemented');

        if ($str == '' || !is_numeric($str))
        {
            throw new \InvalidArgumentException('Invalid input string. Needs to be a valid Exact Complex Radical');
        }
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        if (count($data) !== self::dataByteCount)
        {
            throw new \LengthException('Invalid data array. Needs to contain ' . self::dataByteCount . ' bytes');
        }

        $coeffR = TH_0x1C::makeStringFromData(array_slice($data, 0, TH_0x1C::dataByteCount));
        $coeffI = TH_0x1C::makeStringFromData(array_slice($data, TH_0x1C::dataByteCount, TH_0x1C::dataByteCount));

        $str = '(' . $coeffR . ')+(' . $coeffI . ')*i';

        $str = str_replace('+-', '-', $str);

        return $str;
    }
}
