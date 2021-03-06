<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars\TypeHandlers;

include_once 'ITIVarTypeHandler.php';

// Type Handler for type 0x05: Program
class TH_0x05 implements ITIVarTypeHandler
{
    private static $tokens_BytesToName = [];
    private static $tokens_NameToBytes = [];
    private static $lengthOfLongestTokenName = 0;
    private static $firstByteOfTwoByteTokens = [];
    private static $squishedASMTokens = [ 0xBB6D, 0xEF69, 0xEF7B ]; // 83+/84+, 84+CSE, CE

    /**
     * Tokenizer
     *
     * @param   string  $str        The program source as a string
     * @param   array   $options    Ignored here (French and English token names supported by default)
     * @return  array   The bytes (tokens) array (the two first ones are the size of the rest)
     */
    public static function makeDataFromString($str = '', array $options = [])
    {
        $data = [ 0, 0 ]; // two bytes reserved for the size. Filled later

        $maxTokSearchLen = min(mb_strlen($str), self::$lengthOfLongestTokenName);

        $isWithinString = false;

        for ($strCursorPos = 0, $strCursorLen =  mb_strlen($str); $strCursorPos < $strCursorLen; $strCursorPos++)
        {
            $currChar = mb_substr($str, 1);
            if ($currChar === '"')
            {
                $isWithinString = !$isWithinString;
            } else if ($currChar === "\n" || $currChar === '→')
            {
                $isWithinString = false;
            }
            /* isWithinString => minimum token length, otherwise maximal munch */
            for ($currentLength = $isWithinString ? 1 : $maxTokSearchLen;
                 $isWithinString ? ($currentLength <= $maxTokSearchLen) : ($currentLength > 0);
                 $currentLength += ($isWithinString ? 1 : -1))
            {
                $currentSubString = mb_substr($str, $strCursorPos, $currentLength);
                if (isset(self::$tokens_NameToBytes[$currentSubString]))
                {
                    $tokenValue = self::$tokens_NameToBytes[$currentSubString];
                    ($tokenValue > 0xFF) && array_push($data, $tokenValue >> 8);
                    $data[] = $tokenValue & 0xFF;
                    $strCursorPos += $currentLength - 1;
                    break;
                }
            }
        }

        $actualDataLen = count($data) - 2;
        $data[0] = $actualDataLen & 0xFF;
        $data[1] = ($actualDataLen >> 8) & 0xFF;

        return $data;
    }

    /**
     * Detokenizer
     *
     * @param   array   $data       The bytes (tokens) array
     * @param   array   $options    Associative array of options such as ['lang' => 'fr']
     * @return  string  The program source as a string (detokenized)
     * @throws  \Exception
     */
    public static function makeStringFromData(array $data = [], array $options = [])
    {
        if ($data === [])
        {
            throw new \InvalidArgumentException('Empty data array. Needs to contain at least 2 bytes (size fields)');
        }

        $langIdx = (isset($options['lang']) && $options['lang'] === 'fr') ? 1 : 0;

        $howManyBytes = ($data[0] & 0xFF) + (($data[1] & 0xFF) << 8);
        array_shift($data); array_shift($data);
        if ($howManyBytes !== count($data))
        {
            trigger_error('[Warning] Byte count (' . count($data) . ') and size field (' . $howManyBytes . ') mismatch!');
        }

        $twoFirstBytes = ($data[1] & 0xFF) + (($data[0] & 0xFF) << 8);
        if (in_array($twoFirstBytes, self::$squishedASMTokens, true))
        {
            return '[Error] This is a squished ASM program - cannnot preview it!';
        }

        $errCount = 0;
        $str = '';
        for ($i = 0; $i < $howManyBytes; $i++)
        {
            $currentToken = $data[$i];
            $nextToken = ($i < $howManyBytes-1) ? $data[$i+1] : null;
            $bytesKey = $currentToken;
            if (in_array($currentToken, self::$firstByteOfTwoByteTokens))
            {
                if ($nextToken === null)
                {
                    trigger_error('[Warning] Encountered an unfinished two-byte token! Setting the second byte to 0x00');
                    $nextToken = 0x00;
                }
                $bytesKey = $nextToken + ($currentToken << 8);
                $i++;
            }
            if (isset(self::$tokens_BytesToName[$bytesKey]))
            {
                $str .= self::$tokens_BytesToName[$bytesKey][$langIdx];
            } else  {
                $str .= ' [???] ';
                $errCount++;
            }
        }

        if ($errCount > 0)
        {
            trigger_error("[Warning] {$errCount} token(s) could not be detokenized (' [???] ' was used)!");
        }

        if (isset($options['prettify']) && $options['prettify'] === true)
        {
            $str = preg_replace('/\[?\|?([a-z]+)\]?/', '\1', $str);
        }

        if (isset($options['reindent']) && $options['reindent'] === true)
        {
            $reindent_lang = isset($options['reindent_lang']) ? [ 'lang' => $options['reindent_lang'] ] : [];
            $str = self::reindentCodeString($str, $reindent_lang);
        }

        return $str;
    }

    public static function reindentCodeString($str = '', array $options = [])
    {
        if (isset($options['lang']))
        {
            $lang = $options['lang'];
        } else {
            $lang = (preg_match('/^\.[a-z.]/i', $str) === 1) ? 'Axe' : 'Basic';
        }

        $str = preg_replace('/([\S])(Del|Eff)Var /mi', "$1\n$2Var ", $str);

        $lines = explode("\n", $str);

        // Inplace-replace the appropriate ":" by new-line chars (ie, by inserting the split string in the $lines array)
        for ($idx = 0, $max = count($lines); $idx < $max; $idx++)
        {
            $line = $lines[$idx];
            $isWithinString = false;
            for ($strIdx = 0, $strLen = mb_strlen($line); $strIdx < $strLen; $strIdx++)
            {
                $currChar = mb_substr($line, $strIdx, 1);
                if ($currChar === ':' && !$isWithinString)
                {
                    $lines[$idx] = mb_substr($line, 0, $strIdx); // replace "old" line by lhs
                    array_splice($lines, $idx + 1, 0, mb_substr($line, $strIdx + 1)); // inserting rhs
                    $max++; // the count changed
                    break;
                } elseif ($currChar === '"') {
                    $isWithinString = !$isWithinString;
                } elseif ($currChar === "\n" || $currChar === '→') {
                    $isWithinString = false;
                }
            }
        }

        foreach($lines as $key => $line)
        {
            $lines[$key] = [ 0, $line ]; // indent, text
        }

        $increaseIndentAfter   = ['If', 'For', 'While', 'Repeat'];
        $decreaseIndentOfToken = ['Then', 'Else', 'End', 'ElseIf', 'EndIf', 'End!If'];
        $closingTokens         = ['End', 'EndIf', 'End!If'];
        $nextIndent = 0;
        $oldFirstCommand = $firstCommand = '';
        foreach($lines as $key => $lineData)
        {
            $oldFirstCommand = $firstCommand;

            $trimmedLine = trim($lineData[1]);
            $firstCommand = trim(strtok($trimmedLine, ' '));
            if ($firstCommand === $trimmedLine)
            {
                $firstCommand = trim(strtok($trimmedLine, '('));
            }

            $lines[$key][0] = $nextIndent;

            if (in_array($firstCommand, $increaseIndentAfter, true))
            {
                $nextIndent++;
            }
            if ($lines[$key][0] > 0 && in_array($firstCommand, $decreaseIndentOfToken, true))
            {
                $lines[$key][0]--;
            }
            if ($nextIndent > 0 && (in_array($firstCommand, $closingTokens, true) || ($oldFirstCommand === 'If' && $firstCommand !== 'Then' && $lang !== 'Axe')))
            {
                $nextIndent--;
            }
        }

        $str = '';
        foreach($lines as $line)
        {
            $str .= str_repeat(' ', $line[0]*3) . $line[1] . "\n";
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
                        self::$firstByteOfTwoByteTokens[] = hexdec($tokenInfo[7]);
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
        } else {
            throw new \RuntimeException('Could not open the tokens csv file');
        }
    }
}

TH_0x05::initTokens();