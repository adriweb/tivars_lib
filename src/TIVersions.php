<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars;

abstract class TIVersions
{
    const hasVersionField = 2;
    const hasFlash = 2;
    const hasColor = 4;

    private static $versions = [];

    /**
     *  Make and insert the associative arrays for the version.
     *
     * @param int       $level   The [compatibility] level of this version
     * @param string    $name    The name of the calc using this version
     * @param string    $sig     The signature (magic bytes) used for this version
     */
    private static function insertVersion($level, $name, $sig)
    {
        if (!isset(self::$versions[$name]))
            self::$versions[$name]  = [ 'level' => $level, 'sig'  => $sig ];

        if (!isset(self::$versions[$level]))
            self::$versions[$level] = [ 'name'  => $name,  'sig'  => $sig ];

        if (!isset(self::$versions[$sig]))
            self::$versions[$sig]   = [ 'level' => $level, 'name' => $name ];
    }

    // TODO : Research actual compatibility level/"versions" from libtifiles, and maybe even TI ?
    public static function initTIVersionsArray()
    {
        self::insertVersion(99, 'Unknown', '');

        self::insertVersion(0,  '82',      '**TI82**');
        self::insertVersion(1,  '83',      '**TI83**');
        self::insertVersion(4,  '84+',     '**TI83F*'); // default for this sig
        self::insertVersion(2,  '82A',     '**TI83F*');
        self::insertVersion(3,  '83+',     '**TI83F*');
        self::insertVersion(3,  '82+',     '**TI83F*');
        self::insertVersion(5,  '84+CSE',  '**TI83F*');
        self::insertVersion(6,  '84+CE',   '**TI83F*');
        self::insertVersion(7,  '83PCE',   '**TI83F*');
    }

    /**
     * @param   int     $level  The version level
     * @return  string          The version name for that Level
     */
    public static function getNameFromLevel($level = 99)
    {
        if ($level !== 99 && isset(self::$versions[$level]))
        {
            return self::$versions[$level]['name'];
        } else {
            return 'Unknown';
        }
    }

    /**
     * @param   string  $name   The version name
     * @return  int             The version level for that name
     */
    public static function getLevelFromName($name = '')
    {
        if ($name !== '' && isset(self::$versions[$name]))
        {
            return self::$versions[$name]['level'];
        } else {
            return 99;
        }
    }

    /**
     * @param   int     $level  The version level
     * @return  string          The signature for that Level
     */
    public static function getSignatureFromLevel($level = 99)
    {
        if ($level !== 99 && isset(self::$versions[$level]))
        {
            return self::$versions[$level]['sig'];
        } else {
            return '';
        }
    }

    /**
     * @param   string  $name
     * @return  string          The signature for that name
     */
    public static function getSignatureFromName($name = '')
    {
        if ($name !== '' && isset(self::$versions[$name]))
        {
            return self::$versions[$name]['sig'];
        } else {
            return '';
        }
    }

    /**
     * @param   string  $sig
     * @return  string          The default calc name whose file formats use that signature
     */
    public static function getDefaultNameFromSignature($sig = '')
    {
        if ($sig !== '' && isset(self::$versions[$sig]))
        {
            return self::$versions[$sig]['name'];
        } else {
            return '';
        }
    }

    /**
     * @param   string  $sig    The signature
     * @return  string          The minimum compatibility level for that signaure
     */
    public static function getMinLevelFromSignature($sig = '')
    {
        if ($sig !== '' && isset(self::$versions[$sig]))
        {
            return self::$versions[$sig]['level'];
        } else {
            return 99;
        }
    }

    public static function isValidLevel($level = 99)
    {
        return ($level != 99 && is_int($level) && isset(self::$versions[$level]));
    }

    public static function isValidName($name = '')
    {
        return ($name !== '' && isset(self::$versions[$name]));
    }

    public static function isValidSignature($sig = '')
    {
        return ($sig !== '' && isset(self::$versions[$sig]));
    }
}

TIVersions::initTIVersionsArray();
