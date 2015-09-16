<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars;

class TIVarTypes
{
    private static $types =
    [
        'Unknown'             => -1,

        /* Standard types */
        'Real'                => 0x00,
        'RealList'            => 0x01,
        'Matrix'              => 0x02,
        'Equation'            => 0x03,
        'String'              => 0x04,
        'Program'             => 0x05,
        'ProtectedProgram'    => 0x06,
        'CPicture'            => 0x07,
        'Picture'             => 0x07,
        'GraphDataBase'       => 0x08,
        'NewEquation'         => 0x0B,
        'Complex'             => 0x0C,
        'ComplexList'         => 0x0D,
        'Window'              => 0x0F,
        'RecallWindow'        => 0x10,
        'TableRange'          => 0x11,
        'AppVar'              => 0x15,
        'TemporaryItem'       => 0x16,
        'GroupObject'         => 0x17,
        'CImage'              => 0x1A,

        /* Exact values (TI-83 Premium CE) */
        'ExactComplexFrac'    => 0x1B,
        'ExactRealRadical'    => 0x1C,
        'ExactComplexRadical' => 0x1D,
        'ExactComplexPi'      => 0x1E,
        'ExactComplexPiFrac'  => 0x1F,
        'ExactRealPi'         => 0x20,
        'ExactRealPiFrac'     => 0x21,

        /* System/Flash-related things */
        'OperatingSystem'     => 0x23,
        'FlashApp'            => 0x24,
        'Certificate'         => 0x25,
        'CertificateMemory'   => 0x27,
        'Clock'               => 0x29,
        'FlashLicense'        => 0x3E,

        /* Equations */
        'EquationFunction'    => 0x90,
        'EquationParametric'  => 0x91,
        'EquationPolar'       => 0x92,
        'EquationSequence'    => 0x93,
    ];

    /**
     *  Make a id=>str and str=>id associative array of the types.
     */
    public static function initTIVarTypesArray()
    {
        self::$types += array_flip(self::$types);
    }

    /**
     * @param   int     $id     The type ID
     * @return  string  The type name.
     */
    public static function getTypeStringFromID($id = -1)
    {
        return ($id === -1 || !isset(self::$types[$id])) ? 'Unknown' : self::$types[$id];
    }

    /**
     * @param   string  $str    The type name
     * @return  int     The type ID.
     */
    public static function getTypeIDFromString($str = '')
    {
        return ($str === '' || !isset(self::$types[$str])) ? -1 : self::$types[$str];
    }
}

TIVarTypes::initTIVarTypesArray();
