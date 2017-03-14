<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once 'ITIVarTypeHandler.php';

// Type Handler for type 0x15: AppVar
class TH_0x15 implements ITIVarTypeHandler
{
    public static function makeDataFromString($str = '', array $options = [])
    {
        $formatOk = preg_match('/^([0-9a-fA-F]{2})+$/', $str) === 1;

        $length = strlen($str);
        $bytes = $length / 2;

        if (empty($str) || !$formatOk || $bytes > 0xFFFF)
        {
            throw new \InvalidArgumentException('Invalid input string. Needs to be a valid hex data block');
        }

        $data = [ $bytes & 0xFF, ($bytes >> 8) & 0xFF ];

        for ($i = 0; $i < $length; $i += 2)
        {
            $data[] = hexdec(substr($str, $i, 2));
        }

        return $data;
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        $byteCount = count($data);
        $lengthExp = ($data[0] & 0xFF) + (($data[1] & 0xFF) << 8);
        $lengthDat = $byteCount - 2;

        if ($lengthExp !== $lengthDat)
        {
            throw new \LengthException("Invalid data array. Expected {$lengthExp} bytes, got {$lengthDat}");
        }

        $str = '';

        foreach ($data as $idx => $val)
        {
            if ($idx < 2) { continue; }
            $str .= strtoupper(dechex($val));
        }

        return $str;
    }
}
