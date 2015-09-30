<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/TIs_lib
 * License: MIT
 */

namespace tivars;

class TIVersion
{
    private $name  = 'Unknown';
    private $level = 99;
    private $sig   = '';

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getSig()
    {
        return $this->sig;
    }


    /*** "Constructors" ***/

    /**
     * @param   int     $level  The version compatibliity level
     * @return  TIVersion
     * @throws  \Exception
     */
    public static function createFromLevel($level = -1)
    {
        if (TIVersions::isValidLevel($level))
        {
            $instance = new self();
            $instance->level = $level;
            $instance->sig = TIVersions::getSignatureFromLevel($level);
            $instance->name = TIVersions::getNameFromLevel($level);
            return $instance;
        } else {
            throw new \Exception("Invalid version ID");
        }
    }

    /**
     * @param   string  $name   The version name
     * @return  TIVersion
     * @throws  \Exception
     */
    public static function createFromName($name = '')
    {
        if (TIVersions::isValidName($name))
        {
            $instance = new self();
            $instance->name = $name;
            $instance->level = TIVersions::getLevelFromName($name);
            $instance->sig = TIVersions::getSignatureFromName($name);
            return $instance;
        } else {
            throw new \Exception("Invalid version name");
        }
    }

    /**
     * @param   int     $level  The version compatibliity level
     * @return  TIVersion
     * @throws  \Exception
     */
    public static function createFromSignature($sig = '')
    {
        if (TIVersions::isValidSignature($sig))
        {
            $instance = new self();
            $instance->sig = $sig;
            $instance->level = TIVersions::getMinLevelFromSignature($sig);
            $instance->name = TIVersions::getDefaultNameFromSignature($sig);
            return $instance;
        } else {
            throw new \Exception("Invalid version signature");
        }
    }

}
