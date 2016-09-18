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

// Type Handler for type 0x02: Matrix
class TH_0x02 implements ITIVarTypeHandler
{
    public static function makeDataFromString($str = '', array $options = [])
    {
        if (strlen($str) < 5 || substr($str, 0, 2) !== '[[' || substr($str, -2, 2) !== ']]')
        {
            throw new \Exception("Invalid input string. Needs to be a valid matrix");
        }
        $matrix = explode('][', substr($str, 2, -2));
        $rowCount = count($matrix);
        $colCount = substr_count($matrix[0], ',') + 1;

        if ($colCount > 255 || $rowCount > 255)
        {
            throw new \Exception("Invalid input string. Needs to be a valid matrix (max col/row = 255)");
        }

        foreach ($matrix as &$row)
        {
            $row = explode(',', $row);
            if (count($row) !== $colCount)
            {
                throw new \Exception("Invalid input string. Needs to be a valid matrix (consistent column count)");
            }
        }

        foreach ($matrix as &$row)
        {
            foreach ($row as &$numStr)
            {
                $numStr = trim($numStr);
                if (!is_numeric($numStr))
                {
                    throw new \Exception("Invalid input string. Needs to be a valid matrix (real numbers inside)");
                }
            }
        }

        $data = [];

        $data[0] = $colCount;
        $data[1] = $rowCount;

        foreach ($matrix as &$row)
        {
            foreach ($row as &$numStr)
            {
                $data = array_merge($data, TH_0x00::makeDataFromString($numStr));
            }
        }

        return $data;
    }

    public static function makeStringFromData(array $data = [], array $options = [])
    {
        $byteCount = count($data);
        $colCount = $data[0];
        $rowCount = $data[1];
        if (count($data) < 2+TH_0x00::dataByteCount || $colCount < 1 || $rowCount < 1 || $colCount > 255 || $rowCount > 255
            || (($byteCount - 2) % TH_0x00::dataByteCount !== 0) || ($colCount*$rowCount !== ($byteCount - 2) / TH_0x00::dataByteCount))
        {
            throw new \Exception('Invalid data array. Needs to contain 1+1+' . TH_0x00::dataByteCount . '*n bytes');
        }

        $str = '[';

        for ($i = 2, $num = 0; $i < $byteCount; $i += TH_0x00::dataByteCount, $num++)
        {
            if ($num % $colCount === 0) // first column
            {
                $str .= '[';
            }
            $str .= TH_0x00::makeStringFromData(array_slice($data, $i, TH_0x00::dataByteCount));
            if ($num % $colCount < $colCount - 1) // not last column
            {
                $str .= ',';
            } else {
                $str .= ']';
            }
        }

        $str .= ']';

        // TODO: prettified option

        return $str;
    }
}
