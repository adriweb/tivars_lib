<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

include_once "src/autoloader.php";
include_once "src/TypeHandlers/TH_0x05.php";

use tivars\TIModel;
use tivars\TIVarFile;
use tivars\TIVarType;
use tivars\TIVarTypes;


$newPrgm = TIVarFile::createNew(TIVarType::createFromName("Program"));
$newPrgm->setContentFromString("Asm(prgmABCD");
print_r($newPrgm->getRawContent());

$newPrgm = TIVarFile::createNew(TIVarType::createFromName("Real"));
$newPrgm->setContentFromString("45.2");
print_r($newPrgm->getRawContent());

$testString = TIVarFile::loadFromFile("testData/String.8xs");
assert($testString->getReadableContent() == "Hello World");


$testEquation = TIVarFile::loadFromFile("testData/Equation_Y1T.8xy");
assert($testEquation->getReadableContent() == "3sin(T)+4");


$testReal = TIVarFile::loadFromFile("testData/Real.8xn");
echo "testReal.getReadableContent() : " . $testReal->getReadableContent() . "\n";
assert($testReal->getReadableContent() == "-42.1337");



$testData = tivars\TypeHandlers\TH_0x05::makeDataFromString("\"<\":Asm(prgmABCD");
$goodTypeForCalc = TIVarFile::createNew(TIVarType::createFromName("Program"), "Bla", TIModel::createFromName("83PCE"));
$goodTypeForCalc->setContentFromData($testData);
$test = $goodTypeForCalc->getReadableContent();
$goodTypeForCalc->setContentFromString($test);
echo $goodTypeForCalc->getReadableContent();
//$goodTypeForCalc->saveVarToFile();

$badTypeForCalc = TIVarFile::createNew(TIVarType::createFromName('ExactComplexFrac'), 'Bla', TIModel::createFromName('83PCE'));
try
{
    $goodTypeForCalc = TIVarFile::createNew(TIVarType::createFromName('ExactComplexFrac'), 'Bla', TIModel::createFromName('84+'));
    assert(false);
} catch (Exception $e) {}



assert(TIVarTypes::getIDFromName("ExactRealPi") === 32);


$testPrgmQuotes = TIVarFile::loadFromFile('testData/testPrgmQuotes.8xp');
$testPrgmcontent = $testPrgmQuotes->getReadableContent();
echo "testPrgmContent \$testPrgmQuotes :\n$testPrgmcontent\n";

$testPrgm = TIVarFile::loadFromFile('testData/Program.8xp');
echo "testPrgm->getHeader()['entries_len'] == " . $testPrgm->getHeader()['entries_len'] . "\n";
echo "testPrgm->size() - 57 == " . ($testPrgm->size() - 57) . "\n";

assert($testPrgm->getHeader()['entries_len'] === $testPrgm->size() - 57);
$newPrgm = TIVarFile::createNew(TIVarType::createFromName("Program"));
$testPrgmcontent = $testPrgm->getReadableContent(['lang' => 'fr']);
//echo "testPrgmContent :\n$testPrgmcontent\n";
$newPrgm->setContentFromString($testPrgmcontent);
assert($testPrgm->getRawContent() === $newPrgm->getRawContent());
//$newPrgm->saveVarToFile(".", "asdf");

//die();

$testPrgm = TIVarFile::loadFromFile('testData/ProtectedProgram_long.8xp');
$testPrgmcontent = $testPrgm->getReadableContent(['prettify' => true, 'reindent' => true]);
echo "All prettified and reindented:\n" . $testPrgmcontent . "\n";



$testPrgm = TIVarFile::loadFromFile('testData/Program.8xp');
$newPrgm = TIVarFile::createNew(TIVarType::createFromName("Program"));
$newPrgm->setContentFromString($testPrgm->getReadableContent(['lang' => 'en']));
assert($testPrgm->getRawContent() === $newPrgm->getRawContent());



$testReal = TIVarFile::loadFromFile('testData/Real.8xn'); // -42.1337
$newReal = TIVarFile::createNew(TIVarType::createFromName("Real"), "A");
$newReal->setContentFromString('-42.1337');
assert($testReal->getReadableContent() === '-42.1337');
assert($testReal->getRawContent() === $newReal->getRawContent());
//$newReal->saveVarToFile("/Users/adriweb/", "trololol");



$testExactRealFrac = TIVarFile::loadFromFile('testData/Exact_RealFrac.8xn');
echo "Before: " . $testExactRealFrac->getReadableContent() . "\t" . "Now: ";
$testExactRealFrac->setContentFromString("0.2");
echo $testExactRealFrac->getReadableContent() . "\n";
//$testExactRealFrac->saveVarToFile();



$testRealList = TIVarFile::loadFromFile('testData/RealList.8xl');
echo "Before: " . $testRealList->getReadableContent() . "\t" . "Now: ";
$testRealList->setContentFromString("{-1,2,999}");
echo $testRealList->getReadableContent() . "\n";
$testRealList->saveVarToFile('testData', 'RealList_new');




//$testMatrixStandard = TIVarFile::loadFromFile('testData/Matrix_3x3_standard.8xm');
//print_r($testMatrixStandard);
//echo "Before: " . $testExactRealFrac->getReadableContent() . "\t" . "Now: ";
//$testExactRealFrac->setContentFromString("0.2");
//echo $testExactRealFrac->getReadableContent() . "\n";
//$testExactRealFrac->saveVarToFile();
