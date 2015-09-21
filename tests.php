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


assert(TIVarTypes::getIDFromName("ExactRealPi") === 32);


$testPrgm = TIVarFile::loadFromFile('testData/ProtectedProgram.8xp');
assert($testPrgm->getReadableContent(['lang' => 'en']) === 'Disp "H[|e]llo Wo[r]l[|d]');

//$newPrgm = TIVarFile::createNew(TIVarType::createFromName("Program"));
//$newPrgm->setContentFromString("asdf");
//print_r($newPrgm);


$testReal = TIVarFile::loadFromFile('testData/Real_neg.8xn'); // -42.1337
$newReal = TIVarFile::createNew(TIVarType::createFromName("Real"));
$newReal->setContentFromString('-42.1337');
assert($testReal->getHeader()['entries_len'] === $testReal->size() - 57);
assert($testReal->getReadableContent() === '-42.1337');
assert($testReal->getRawContent() === $newReal->getRawContent());

?>