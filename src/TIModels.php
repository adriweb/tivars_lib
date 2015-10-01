<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars;

abstract class TIFeatureFlags
{
    const has82things  = (1 << 0);
    const hasComplex   = (1 << 1);
    const hasFlash     = (1 << 2);
    const hasApps      = (1 << 3);
    const hasClock     = (1 << 4);
    const hasColorLCD  = (1 << 5);
    const hasEZ80CPU   = (1 << 6);
    const hasExactMath = (1 << 7);
}

abstract class TIModels
{
    private static $models = [];

    /**
     *  Make and insert the associative arrays for the model.
     *
     * @param int       $flags   The flags determining available features
     * @param string    $name    The name of the calc using this model
     * @param string    $sig     The signature (magic bytes) used for this model
     */
    private static function insertModel($flags, $name, $sig)
    {
        if (!isset(self::$models[$name]))
            self::$models[$name]  = [ 'flags'  => $flags, 'sig'  => $sig ];

        if (!isset(self::$models[$flags]))
            self::$models[$flags] = [ 'name'   => $name,  'sig'  => $sig ];

        if (!isset(self::$models[$sig]))
            self::$models[$sig]   = [ 'flags'  => $flags, 'name' => $name ];
    }

    // TODO : Research actual compatibility flags/"versions" from libtifiles, and maybe even TI ?
    public static function initTIModelsArray()
    {
        $flags82     = 0            | TIFeatureFlags::has82things;
        $flags83     = $flags82     | TIFeatureFlags::hasComplex;
        $flags82a    = $flags83     | TIFeatureFlags::hasFlash;
        $flags83p    = $flags82a    | TIFeatureFlags::hasApps;
        $flags84p    = $flags83p    | TIFeatureFlags::hasClock;
        $flags84pcse = $flags84p    | TIFeatureFlags::hasColorLCD;
        $flags84pce  = $flags84pcse | TIFeatureFlags::hasEZ80CPU;
        $flags83pce  = $flags84pce  | TIFeatureFlags::hasExactMath;

        self::insertModel(0,            'Unknown', '');
        self::insertModel($flags82,     '82',      '**TI82**');
        self::insertModel($flags83,     '83',      '**TI83**');
        self::insertModel($flags82a,    '82A',     '**TI83F*');
        self::insertModel($flags83p,    '82+',     '**TI83F*');
        self::insertModel($flags83p,    '83+',     '**TI83F*');
        self::insertModel($flags84p,    '84+',     '**TI83F*');
        self::insertModel($flags84pcse, '84+CSE',  '**TI83F*');
        self::insertModel($flags84pce,  '84+CE',   '**TI83F*');
        self::insertModel($flags83pce,  '83PCE',   '**TI83F*');
    }

    /**
     * @param   int     $flags  The model flags
     * @return  string          The model name for those flags
     */
    public static function getDefaultNameFromFlags($flags = 0)
    {
        return self::isValidFlags($flags) ? self::$models[$flags]['name'] : 'Unknown';
    }

    /**
     * @param   string  $name   The model name
     * @return  int             The model flags for that name
     */
    public static function getFlagsFromName($name = '')
    {
        return self::isValidName($name) ? self::$models[$name]['flags'] : 0;
    }

    /**
     * @param   int     $flags  The model flags
     * @return  string          The signature for those flags
     */
    public static function getSignatureFromFlags($flags = 0)
    {
        return self::isValidFlags($flags) ? self::$models[$flags]['sig'] : '';
    }

    /**
     * @param   string  $name
     * @return  string          The signature for that name
     */
    public static function getSignatureFromName($name = '')
    {
        return self::isValidName($name) ? self::$models[$name]['sig'] : '';
    }

    /**
     * @param   string  $sig    The signature
     * @return  string          The default calc name whose file formats use that signature
     */
    public static function getDefaultNameFromSignature($sig = '')
    {
        return self::isValidSignature($sig) ? self::$models[$sig]['name'] : '';
    }

    /**
     * @param   string  $sig    The signature
     * @return  string          The minimum compatibility flags for that signaure
     */
    public static function getMinFlagsFromSignature($sig = '')
    {
        return self::isValidSignature($sig) ? self::$models[$sig]['flags'] : 0;
    }


    public static function isValidFlags($flags = 0)
    {
        return ($flags !== 0 && isset(self::$models[$flags]));
    }

    public static function isValidName($name = '')
    {
        return ($name !== '' && isset(self::$models[$name]));
    }

    public static function isValidSignature($sig = '')
    {
        return ($sig !== '' && isset(self::$models[$sig]));
    }
}

TIModels::initTIModelsArray();