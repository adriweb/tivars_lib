<?php
/*
 * Part of tivars_lib
 * (C) 2015-2016 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once "ITIVarTypeHandler.php";

// Type Handler for type 0x0C: Complex Number
class TH_0x0C implements ITIVarTypeHandler
{
    const dataByteCount = 9;

    public static function makeDataFromString($str = '', array $options = [])
    {
        $str = str_replace(' ', '', $str);
        $str = str_replace('+i', '+1i', $str);
        $str = str_replace('-i', '-1i', $str);

        // Handle real only, real+imag, image only.
        $isCplx = preg_match('/^'   . TH_0x00::validPattern . '()$/i', $str, $matches)
               || preg_match('/^'   . TH_0x00::validPattern . TH_0x00::validPattern . 'i$/i', $str, $matches)
               || preg_match('/^()' . TH_0x00::validPattern . 'i$/i', $str, $matches);

        if ($isCplx !== true)
        {
            throw new \Exception("Invalid input string. Needs to be a valid complex number (a+bi)");
        }

        $data = [];

        $coeffs = [
            /* real coeff */ (float)$matches[1],
            /* imag coeff */ (float)$matches[2]
        ];

        for ($i=0; $i<2; $i++)
        {
            $coeff = $coeffs[$i];

            $data = array_merge($data, TH_0x00::makeDataFromString($coeff));

            $flags = 0;
            $flags |= ($coeff < 0) ? (1 << 7) : 0;
            $flags |= (1 << 2); // Because it's a complex number
            $flags |= (1 << 3); // Because it's a complex number

            $data[$i * TH_0x00::dataByteCount] = $flags;
        }

        return $data;
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        if (count($data) !== 2 * TH_0x00::dataByteCount)
        {
            throw new \Exception('Invalid data array. Needs to contain ' . 2 * TH_0x00::dataByteCount . ' bytes');
        }

        $coeffR = TH_0x00::makeStringFromData(array_slice($data, 0, TH_0x00::dataByteCount));
        $coeffI = TH_0x00::makeStringFromData(array_slice($data, TH_0x00::dataByteCount, TH_0x00::dataByteCount));

        $str = $coeffR . '+' . $coeffI . 'i';
        $str = str_replace('+-', '-', $str);

        return $str;
    }
}
