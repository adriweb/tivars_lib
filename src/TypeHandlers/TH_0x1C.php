<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once 'ITIVarTypeHandler.php';

// Type Handler for type 0x1C: ExactRealRadical
class TH_0x1C implements ITIVarTypeHandler
{
    const dataByteCount = 9;

    public static function makeDataFromString($str = '', array $options = [])
    {
        throw new \BadMethodCallException('Unimplemented');

        if ($str == '' || !is_numeric($str))
        {
            throw new \InvalidArgumentException('Invalid input string. Needs to be a valid Exact Real Radical');
        }
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        if (count($data) !== self::dataByteCount)
        {
            throw new \LengthException('Invalid data array. Needs to contain ' . self::dataByteCount . ' bytes');
        }

        $dataStr = '';
        foreach ($data as $val)
        {
            $dataStr .= sprintf('%02X', $val);
        }

        $type = substr($dataStr, 0, 2);
        if (!($type === '1C' || $type === '1D')) // real or complex (two reals, see TH_1D)
        {
            throw new \InvalidArgumentException('Invalid data bytes - invalid vartype: ' . $type);
        }

        $subtype = $dataStr[2];
        if ($subtype < 0 || $subtype > 3)
        {
            throw new \InvalidArgumentException('Invalid data bytes - unknown subtype: ' . $subtype);
        }

        $parts = [
            ($subtype == 1 || $subtype == 3 ? '-' : '')  . (string)((int)substr($dataStr, 9, 3)),
            (string)((int)substr($dataStr, 15, 3)),
            ($subtype == 2 || $subtype == 3 ? '-' : '+') . (string)((int)substr($dataStr, 6, 3)),
            (string)((int)substr($dataStr, 12, 3)),
            (string)((int)substr($dataStr,  3, 3))
        ];

        $str = '(' . $parts[0] . '*√(' . $parts[1] .')' . $parts[2] . '*√(' . $parts[3] .'))/'. $parts[4];

        // Improve final display
        $str = str_replace('+1*', '+', $str); $str = str_replace('(1*',  '(',  $str);
        $str = str_replace('-1*', '-', $str); $str = str_replace('(-1*', '(-', $str);
        $str = str_replace('+-',  '-', $str);

        return $str;
    }
}
