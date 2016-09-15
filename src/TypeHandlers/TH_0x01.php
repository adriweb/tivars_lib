<?php
/*
 * Part of tivars_lib
 * (C) 2016 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once "ITIVarTypeHandler.php";
include_once "TH_0x00.php";

// Type Handler for type 0x01: Real list
class TH_0x01 implements ITIVarTypeHandler
{
    public static function makeDataFromString($str = '', array $options = [])
    {
        $arr = explode(',', trim($str, '{}'));
        $numCount = count($arr);

        $formatOk = true;
        foreach ($arr as &$numStr)
        {
            $numStr = trim($numStr);
            if (!is_numeric($numStr))
            {
                $formatOk = false;
                break;
            }
        }
        if ($str == '' || empty($arr) || !$formatOk || $numCount > 999)
        {
            throw new \Exception("Invalid input string. Needs to be a valid real list");
        }

        $data = [];

        $data[0] = $numCount & 0xFF;
        $data[1] = ($numCount >> 8) & 0xFF;

        foreach ($arr as &$numStr)
        {
            $data = array_merge($data, TH_0x00::makeDataFromString($numStr));
        }

        return $data;
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        $byteCount = count($data);
        $numCount = ($data[0] & 0xFF) + (($data[1] << 8) & 0xFF00);
        if (count($data) < 2 || (($byteCount - 2) % TH_0x00::dataByteCount !== 0) || ($numCount !== ($byteCount - 2) / TH_0x00::dataByteCount) || $numCount > 999)
        {
            throw new \Exception('Invalid data array. Needs to contain 2+' . TH_0x00::dataByteCount . '*n bytes');
        }

        $str = '{';

        for ($i = 2, $num = 0; $i < $byteCount; $i += TH_0x00::dataByteCount, $num++)
        {
            $str .= TH_0x00::makeStringFromData(array_slice($data, $i, TH_0x00::dataByteCount));
            if ($num < $numCount - 1) // not last num
            {
                $str .= ',';
            }
        }

        $str .= '}';

        return $str;
    }
}
