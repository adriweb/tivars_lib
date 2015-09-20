<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once "ITIVarTypeHandler.php";

// Type Handler for type 0x05: Program
class TH_0x05 implements ITIVarTypeHandler
{
    /**
     * Tokenizer
     * @param   string  $str        The program source as a string
     * @param   array   $options    Associative array of options such as ['lang' => 'fr']
     * @return  array   The bytes (tokens) array
     */
    public function makeDataFromString($str = '', array $options = [])
    {
        // TODO: tokenize.
    }


    /**
     * Detokenizer
     * @param   array   $data       The bytes (tokens) array
     * @param   array   $options    Associative array of options such as ['lang' => 'fr']
     * @return  string  The program source as a string (detokenized)
     */
    public function makeStringFromData(array $data = [], array $options = [])
    {
        // TODO: detokenize.
    }
}