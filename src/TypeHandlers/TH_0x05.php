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
    private static $tokens_BytesToName = [];
    private static $tokens_NameToBytes = [];
    private static $lengthOfLongestTokenName = 0;
    private static $firstByteOfTwoByteTokens = [];

    /**
     * Tokenizer
     * @param   string  $str        The program source as a string
     * @param   array   $options    Associative array of options such as ['lang' => 'fr']
     * @return  array   The bytes (tokens) array
     */
    public function makeDataFromString($str = '', array $options = [])
    {
        // TODO: tokenize.
        return [];
    }


    /**
     * Detokenizer
     *
     * @param   array   $data       The bytes (tokens) array
     * @param   array   $options    Associative array of options such as ['lang' => 'fr']
     * @return  string  The program source as a string (detokenized)
     * @throws  \Exception
     */
    public function makeStringFromData(array $data = [], array $options = [])
    {
        if ($data === [])
        {
            throw new \Exception("Empty data array. Needs to contain at least 2 bytes (size fields)");
        }

        $langIdx = (isset($options['lang']) && $options['lang'] === 'fr') ? 1 : 0;

        $howManyBytes = $data[0] + ($data[1] << 8);
        array_shift($data); array_shift($data);
        if ($howManyBytes !== count($data))
        {
            echo "[Warning] Token count (" . count($data) . ") and size field (" . $howManyBytes . ") mismatch!";
        }

        $str = '';
        for ($i = 0; $i<$howManyBytes; $i++)
        {
            $currentToken = $data[$i];
            $nextToken = ($i < $howManyBytes) ? $data[$i+1] : null;
            $bytesKey = $currentToken;
            if (in_array($currentToken, self::$firstByteOfTwoByteTokens))
            {
                if ($nextToken === null)
                {
                    echo "[Warning] Encountered an unfinished two-byte token! Setting the second byte to 0x00\n";
                    $nextToken = 0x00;
                }
                $bytesKey = $nextToken + ($currentToken << 8);
                $i++;
            }
            $str .= isset(self::$tokens_BytesToName[$bytesKey]) ? self::$tokens_BytesToName[$bytesKey][$langIdx] : ' [???] ';
        }

        return $str;
    }


    public static function initTokens()
    {
        if (($handle = fopen(__DIR__ . '/programs_tokens.csv', 'r')) !== false)
        {
            fgetcsv($handle); // skip first line (header)
            while (($tokenInfo = fgetcsv($handle)) !== false)
            {
                if ($tokenInfo[6] === '2')
                {
                    if (!in_array(hexdec($tokenInfo[7]), self::$firstByteOfTwoByteTokens))
                    {
                        array_push(self::$firstByteOfTwoByteTokens, hexdec($tokenInfo[7]));
                    }
                    $bytes = hexdec($tokenInfo[8]) + (hexdec($tokenInfo[7]) << 8);
                } else {
                    $bytes = hexdec($tokenInfo[7]);
                }
                self::$tokens_BytesToName[$bytes] = [ $tokenInfo[4], $tokenInfo[5] ]; // EN, FR
                self::$tokens_NameToBytes[$tokenInfo[4]] = $bytes; // EN
                self::$tokens_NameToBytes[$tokenInfo[5]] = $bytes; // FR
                $maxLenName = max(mb_strlen($tokenInfo[4], 'UTF-8'), mb_strlen($tokenInfo[5], 'UTF-8'));
                if ($maxLenName > self::$lengthOfLongestTokenName)
                {
                    self::$lengthOfLongestTokenName = $maxLenName;
                }
            }
            fclose($handle);
        }
    }
}

TH_0x05::initTokens();