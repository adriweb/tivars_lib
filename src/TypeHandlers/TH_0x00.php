<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once "ITIVarTypeHandler.php";

// Type Handler for type 0x00: Real
class TH_0x00 implements ITIVarTypeHandler
{

    public function makeDataFromString($str = '', array $options = [])
    {
        // TODO: Implement makeDataFromString() method.
    }

    public function makeStringFromData(array $data = [], array $options = [])
    {
        if (count($data) !== 9)
        {
            throw new \Exception("Invalid data array. Needs to contain 9 bytes");
        }
        $flags      = $data[0];
        $isNegative = ($flags >> 7 === 1);
//      $isUndef    = ($flags  & 1 === 1); // if true, "used for initial sequence values"
        $exponent   = $data[1] - 0x80;
        $number     = '';
        for ($i = 2; $i < 8; $i++)
        {
            $number .= dechex($data[$i]);
        }
        $number = substr($number, 0, 1) . '.' . substr($number, 1);
        $number = ($isNegative ? -1 : 1) * pow(10, $exponent) * ((float)$number);

        return (string)$number;
    }
}
