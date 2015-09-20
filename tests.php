<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

include_once "src/autoloader.php";

use tivars\TIVarFile;
use tivars\TIVarType;
use tivars\TIVarTypes;


/* Types */

/*
$expected = 32;
$actual = TIVarTypes::getIDFromName("ExactRealPi");
echo "$expected === " . 'TIVarTypes::getTypeIdFromString("ExactRealPi")' . " ?\t" . ($expected === $actual ? 'true' : 'false') . "\n";
*/


/* File reading */

/*
$testPrgm = TIVarFile::loadFromFile('testData/ProtectedProgram.8xp');
print_r($testPrgm);
echo "Readable content\n" . $testPrgm->getReadableContent(['lang' => 'en']) . "\n";

//$newPrgm = TIVarFile::createNew(TIVarType::createFromName("Program"));
//$newPrgm->setContentFromString("asdf");
//print_r($newPrgm);
*/

$testReal = TIVarFile::loadFromFile('testData/Real_negative.8xn');
//print_r($testReal);
//echo "Check: filesize-57 == header['entries_len'] ?  " . (($testReal->size() - 57 == $testReal->getHeader()['entries_len']) ? 'true' : 'false') . "\n";
echo "Readable content: " . $testReal->getReadableContent() . "\n";


?>