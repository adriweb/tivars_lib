<?php
/*
 * Part of tivars_lib
 * (C) 2015-2016 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

/*
 TODO:
  - varname restrictions for lists, matrix, strings...
*/

include_once "src/autoloader.php";
include_once "src/TypeHandlers/TH_0x05.php";

use tivars\TIModel;
use tivars\TIVarFile;
use tivars\TIVarType;
use tivars\TIVarTypes;


$testReal42 = TIVarFile::createNew(TIVarType::createFromName("Real"), "R");
$testReal42->setCalcModel(TIModel::createFromName("84+"));
$testReal42->setContentFromString('9001.42');
assert($testReal42->getReadableContent() === '9001.42');
$testReal42->setContentFromString('-0.00000008');
assert((float)$testReal42->getReadableContent() === -8e-08);


$newPrgm = TIVarFile::createNew(TIVarType::createFromName("Program"));
$newPrgm->setContentFromString("Asm(prgmABCD");
print_r($newPrgm->getRawContent());

$newReal = TIVarFile::createNew(TIVarType::createFromName("Real"));
$newReal->setContentFromString(".5");
echo "testReal.getReadableContent() : " . $newReal->getReadableContent() . "\n";
print_r($newReal->getRawContent());

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
$testRealList->setContentFromString("{9, 0, .5, -6e-8}");
echo $testRealList->getReadableContent() . "\n";
//$testRealList->saveVarToFile('testData', 'RealList_new');



$testStandardMatrix = TIVarFile::loadFromFile('testData/Matrix_3x3_standard.8xm');
echo "Before: " . $testStandardMatrix->getReadableContent() . "\t" . "Now: ";
$testStandardMatrix->setContentFromString("[[1,2,3][4,5,6][-7,-8,-9]]");
$testStandardMatrix->setContentFromString("[[1,2,3][4,5,6][-7,-8,-9][1,2,3][4,5,6][-7,-8,-9]]");
echo $testStandardMatrix->getReadableContent() . "\n";
//$testStandardMatrix->saveVarToFile('testData', 'Matrix_new');



$testComplex = TIVarFile::loadFromFile('testData/Complex.8xc'); // -5 + 2i
echo $testComplex->getReadableContent() . "\n";
assert($testComplex->getReadableContent() === '-5+2i');
$newComplex = TIVarFile::createNew(TIVarType::createFromName("Complex"), "C");
$newComplex->setContentFromString('-5+2i');
assert($testComplex->getRawContent() === $newComplex->getRawContent());
$newComplex->setContentFromString('2.5+0.001i');
echo $newComplex->getReadableContent() . "\n";
//$newComplex->saveVarToFile("/Users/adriweb/", "trololol");



//$testMatrixStandard = TIVarFile::loadFromFile('testData/Matrix_3x3_standard.8xm');
//print_r($testMatrixStandard);
//echo "Before: " . $testExactRealFrac->getReadableContent() . "\t" . "Now: ";
//$testExactRealFrac->setContentFromString("0.2");
//echo $testExactRealFrac->getReadableContent() . "\n";
//$testExactRealFrac->saveVarToFile();
