<?php
/*
 * Part of tivars_lib
 * (C) 2015-2016 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once "ITIVarTypeHandler.php";

// Type Handler for type 0x21: ExactRealPiFrac
class TH_0x21 implements ITIVarTypeHandler
{
    const dataByteCount = 9;

    public static function makeDataFromString($str = '', array $options = [])
    {
        if ($str == '' || !is_numeric($str))
        {
            throw new \Exception("Invalid input string. Needs to be a valid Exact Pi Fraction");
        }

        throw new \Exception("Unimplemented");
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        if (count($data) !== self::dataByteCount)
        {
            throw new \Exception('Invalid data array. Needs to contain ' . self::dataByteCount . ' bytes');
        }

        $coeffR = TH_0x00::makeStringFromData(array_slice($data, 0, TH_0x00::dataByteCount));

        $str = ($coeffR !== '0') ? (dec2frac($coeffR) . '*π')  : '0';

        // Improve final display
        $str = str_replace('+1*', '+', $str); $str = str_replace('(1*',  '(',  $str);
        $str = str_replace('-1*', '-', $str); $str = str_replace('(-1*', '(-', $str);
        $str = str_replace('+-',  '-', $str);

        return $str;
    }
}
