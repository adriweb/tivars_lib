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

/* TODO: Use PHPUnit */

/* Types */
$expected = 32;
$actual = TIVarType::getIDFromName("ExactRealPi");
echo "$expected === " . 'TIVarTypes::getTypeIdFromString("ExactRealPi")' . " ?\t" . ($expected === $actual ? 'true' : 'false') . "\n";


/* File reading */
$testPrgm = new TIVarFile('testData/HelloWorld.8xp');
$checksum1 = $testPrgm->computeChecksum();
$checksum2 = $testPrgm->getInFileChecksum();
$valid = $testPrgm->isValid() ? 'true' : 'false';
echo "Computed checksum: {$checksum1} = 0x"  . dechex($checksum1) . "\n";
echo "In file checksum: {$checksum2} = 0x"  . dechex($checksum2) . "\n";
echo "Valid: {$valid}\n";
print_r($testPrgm->getHeader());
echo "Check: filesize-57 == header['entries_len'] ?  " . (($testPrgm->size() - 57 == $testPrgm->getHeader()['entries_len']) ? 'true' : 'false') . "\n";
print_r($testPrgm->getVarEntry());

$testPrgm->fixChecksum();

print_r($testPrgm->getType());
?>