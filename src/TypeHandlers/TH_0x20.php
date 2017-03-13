<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once 'ITIVarTypeHandler.php';

// Type Handler for type 0x20: ExactRealPi
class TH_0x20 implements ITIVarTypeHandler
{
    const dataByteCount = 9;

    public static function makeDataFromString($str = '', array $options = [])
    {
        if ($str == '' || !is_numeric($str))
        {
            throw new \InvalidArgumentException('Invalid input string. Needs to be a valid Exact Pi number');
        }

        throw new \BadMethodCallException('Unimplemented');
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        if (count($data) !== self::dataByteCount)
        {
            throw new \LengthException('Invalid data array. Needs to contain ' . self::dataByteCount . ' bytes');
        }

        $coeff = TH_0x00::makeStringFromData(array_slice($data, 0, TH_0x00::dataByteCount));

        $str = ($coeff !== '0') ? ($coeff . '*π') : '0';

        // Improve final display
        $str = str_replace('+1*', '+', $str); $str = str_replace('(1*',  '(',  $str);
        $str = str_replace('-1*', '-', $str); $str = str_replace('(-1*', '(-', $str);
        $str = str_replace('+-',  '-', $str);

        return $str;
    }
}
