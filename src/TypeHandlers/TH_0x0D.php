<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once 'ITIVarTypeHandler.php';
include_once 'TH_0x0C.php';

// Type Handler for type 0x0D: Complex list
class TH_0x0D implements ITIVarTypeHandler
{
    public static function makeDataFromString($str = '', array $options = [])
    {
        $arr = explode(',', trim($str, '{}'));
        $numCount = count($arr);

        $formatOk = true;
        foreach ($arr as &$numStr)
        {
            $numStr = trim($numStr);
            if (!TH_0x0C::checkValidString($numStr))
            {
                $formatOk = false;
                break;
            }
        }
        if ($str == '' || empty($arr) || !$formatOk || $numCount > 999)
        {
            throw new \InvalidArgumentException('Invalid input string. Needs to be a valid complex list');
        }

        $data = [];

        $data[0] = $numCount & 0xFF;
        $data[1] = ($numCount >> 8) & 0xFF;

        foreach ($arr as &$numStr)
        {
            $data = array_merge($data, TH_0x0C::makeDataFromString($numStr));
        }

        return $data;
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        $byteCount = count($data);
        $numCount = ($data[0] & 0xFF) + (($data[1] & 0xFF) << 8);
        if ($byteCount < 2+TH_0x0C::dataByteCount || (($byteCount - 2) % TH_0x0C::dataByteCount !== 0)
            || ($numCount !== (int)(($byteCount - 2) / TH_0x0C::dataByteCount)) || $numCount > 999)
        {
            throw new \LengthException('Invalid data array. Needs to contain 2+' . TH_0x0C::dataByteCount . '*n bytes');
        }

        $str = '{';

        for ($i = 2, $num = 0; $i < $byteCount; $i += TH_0x0C::dataByteCount, $num++)
        {
            $str .= TH_0x0C::makeStringFromData(array_slice($data, $i, TH_0x0C::dataByteCount));
            if ($num < $numCount - 1) // not last num
            {
                $str .= ',';
            }
        }

        $str .= '}';

        return $str;
    }
}
