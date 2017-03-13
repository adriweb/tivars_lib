<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once 'ITIVarTypeHandler.php';

// Type Handler for type 0x1E: ExactComplexPi
class TH_0x1E implements ITIVarTypeHandler
{
    /* Hardcoded to keep PHP 5.5 compatibilty */
    const dataByteCount = 18; // 2 * TH_0x00::dataByteCount;

    public static function makeDataFromString($str = '', array $options = [])
    {
        if ($str == '' || !is_numeric($str))
        {
            throw new \InvalidArgumentException('Invalid input string. Needs to be a valid Exact Complex Pi number');
        }

        throw new \BadMethodCallException('Unimplemented');
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        if (count($data) !== self::dataByteCount)
        {
            throw new \LengthException('Invalid data array. Needs to contain ' . self::dataByteCount . ' bytes');
        }

        $coeffR = TH_0x00::makeStringFromData(array_slice($data, 0, TH_0x00::dataByteCount));
        $coeffI = TH_0x00::makeStringFromData(array_slice($data, TH_0x00::dataByteCount, TH_0x00::dataByteCount));

        $str = (($coeffR !== '0') ? (dec2frac($coeffR) . '+') : '')
             . (($coeffI !== '0') ? ($coeffI . '*π*i') : '');

        // Improve final display
        $str = str_replace('+1*', '+', $str); $str = str_replace('(1*',  '(',  $str);
        $str = str_replace('-1*', '-', $str); $str = str_replace('(-1*', '(-', $str);
        $str = str_replace('+-',  '-', $str);

        // Shouldn't happen - I don't believe the calc generate such files.
        if ($str === '')
        {
            $str = '0';
        }

        return $str;
    }
}
