<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

/*
 * TODO:
 *
 * - varname restrictions for lists, matrix, strings...
 *
 * - From Lionel: arbitraty storage/retrieval support. Technically this is already "supported" as the user can already fill the data with whatever bytes.
 *                But support could be improved: this could go into appvars (obvious choice), strings, and also pictures and images (for the 84+CSE and CE).
 *                setContentFromData/getRawContent + setContentFromString/getReadableContent should be good to use in these cases (=> new typeHandlers/vartypes)
 *                Watch out for NUL bytes in strings, they've been known to not be liked much by some versions of TI-Connect, at least on the 68k.
*/

include_once 'src/autoloader.php';
include_once 'src/TypeHandlers/TH_0x05.php';

use tivars\TIModel;
use tivars\TIVarFile;
use tivars\TIVarType;
use tivars\TIVarTypes;


$newPrgm = TIVarFile::createNew(TIVarType::createFromName('Program'), 'TESTTOK');
$arr = [
        0xBB, 0xED,
        0xBB, 0xEE,
        0xBB, 0xF2,
        0xBB, 0xF3,
];

array_unshift($arr, count($arr), 0);
$newPrgm->setContentFromData($arr);
$newPrgm->saveVarToFile('testData', 'testtoken');
$testString = TIVarFile::loadFromFile('testData/testtoken.8xp');
assert(strpos($testString->getReadableContent(), '[???]') === false);

$testString = TIVarFile::loadFromFile('testData/ALLCHARS.8Xp');
assert(strpos($testString->getReadableContent(), '[???]') === false);


$testAppVar = TIVarFile::createNew(TIVarType::createFromName('AppVar'), 'TEST');
$testAppVar->setContentFromString('ABCD1234C9C8C7C6'); // random but valid hex string
assert($testAppVar->getReadableContent() === 'ABCD1234C9C8C7C6');
assert(count($testAppVar->getRawContent()) === strlen('ABCD1234C9C8C7C6') / 2 + 2);
//$testAppVar->saveVarToFile('testData', 'testAVnew');


$testReal42 = TIVarFile::createNew(TIVarType::createFromName('Real'), 'R');
$testReal42->setCalcModel(TIModel::createFromName('84+'));
$testReal42->setContentFromString('9001.42');
assert($testReal42->getReadableContent() === '9001.42');
$testReal42->setContentFromString('-0.00000008');
assert((float)$testReal42->getReadableContent() === -8e-08);



$newPrgm = TIVarFile::createNew(TIVarType::createFromName('Program'));
$newPrgm->setContentFromString('Asm(prgmABCD');
print_r($newPrgm->getRawContent());



$testPrgm42 = TIVarFile::createNew(TIVarType::createFromName('Program'), 'asdf');
$testPrgm42->setCalcModel(TIModel::createFromName('82'));
$testPrgm42->setContentFromString('');
$testPrgm42->setVarName('Toto');
//$testPrgm42->saveVarToFile("testData", "blablaTOTO_new");


$newReal = TIVarFile::createNew(TIVarType::createFromName('Real'));
$newReal->setContentFromString('.5');
echo 'testReal.getReadableContent() : ' . $newReal->getReadableContent() . "\n";
print_r($newReal->getRawContent());



$testString = TIVarFile::loadFromFile('testData/String.8xs');
assert($testString->getReadableContent() == 'Hello World');



$testEquation = TIVarFile::loadFromFile('testData/Equation_Y1T.8xy');
assert($testEquation->getReadableContent() == '3sin(T)+4');



$testReal = TIVarFile::loadFromFile('testData/Real.8xn');
echo 'testReal.getReadableContent() : ' . $testReal->getReadableContent() . "\n";
assert($testReal->getReadableContent() == '-42.1337');



$testData = tivars\TypeHandlers\TH_0x05::makeDataFromString('"<":Asm(prgmABCD');
$goodTypeForCalc = TIVarFile::createNew(TIVarType::createFromName('Program'), 'Bla', TIModel::createFromName('83PCE'));
$goodTypeForCalc->setContentFromData($testData);
$test = $goodTypeForCalc->getReadableContent();
$goodTypeForCalc->setContentFromString($test);
echo $goodTypeForCalc->getReadableContent() . "\n";
//$goodTypeForCalc->saveVarToFile();

$badTypeForCalc = TIVarFile::createNew(TIVarType::createFromName('ExactComplexFrac'), 'Bla', TIModel::createFromName('83PCE'));
try
{
    $goodTypeForCalc = TIVarFile::createNew(TIVarType::createFromName('ExactComplexFrac'), 'Bla', TIModel::createFromName('84+'));
    assert(false);
} catch (Exception $e) {}



assert(TIVarTypes::getIDFromName('ExactRealPi') === 32);



$testPrgm = TIVarFile::loadFromFile('testData/Program.8xp');
echo "testPrgm->getHeader()['entries_len'] == " . $testPrgm->getHeader()['entries_len'] . "\n";
echo 'testPrgm->size() - 57 == ' . ($testPrgm->size() - 57) . "\n";

assert($testPrgm->getHeader()['entries_len'] === $testPrgm->size() - 57);
$newPrgm = TIVarFile::createNew(TIVarType::createFromName('Program'));
$testPrgmcontent = $testPrgm->getReadableContent(['lang' => 'fr']);
//echo "testPrgmContent :\n$testPrgmcontent\n";
$newPrgm->setContentFromString($testPrgmcontent);
assert($testPrgm->getRawContent() === $newPrgm->getRawContent());



$testPrgm = TIVarFile::loadFromFile('testData/ProtectedProgram_long.8xp');
$testPrgmcontent = $testPrgm->getReadableContent(['prettify' => true, 'reindent' => true]);
//echo "All prettified and reindented:\n" . $testPrgmcontent . "\n";
// For HTML output:
// echo '<pre><code>' . htmlentities($testPrgmcontent, ENT_QUOTES) . '</code></pre>';
//$testPrgm->saveVarToFile("testData", "ProtectedProgram_long_Resaved");



$testPrgm = TIVarFile::loadFromFile('testData/Program.8xp');
$newPrgm = TIVarFile::createNew(TIVarType::createFromName('Program'));
$newPrgm->setContentFromString($testPrgm->getReadableContent(['lang' => 'en']));
assert($testPrgm->getRawContent() === $newPrgm->getRawContent());
//$newPrgm->saveVarToFile("testData", "Program_new");



$testPrgm42 = TIVarFile::createNew(TIVarType::createFromName('Program'), 'asdf');
$testPrgm42->setCalcModel(TIModel::createFromName('82A'));
$testPrgm42->setContentFromString('Grande blabla:Disp "Grande blabla');
$testPrgm42->setVarName('Toto');
assert($testPrgm42->getReadableContent() == 'Grande blabla:Disp "Grande blabla');
//$testPrgm42->saveVarToFile("testData", "testMinTok_new");

$testPrgm42->setArchived(true);
assert($testPrgm42->getReadableContent() == 'Grande blabla:Disp "Grande blabla');
//$testPrgm42->saveVarToFile("testData", "testMinTok_archived_new");



$testReal = TIVarFile::loadFromFile('testData/Real.8xn'); // -42.1337
$newReal = TIVarFile::createNew(TIVarType::createFromName('Real'), 'A');
$newReal->setContentFromString('-42.1337');
assert($testReal->getReadableContent() === '-42.1337');
assert($testReal->getRawContent() === $newReal->getRawContent());
//$newReal->saveVarToFile("testData", "Real_new");



$testRealList = TIVarFile::loadFromFile('testData/RealList.8xl');
echo 'Before: ' . $testRealList->getReadableContent() . "\n   Now: ";
$testRealList->setContentFromString('{9, 0, .5, -6e-8}');
echo $testRealList->getReadableContent() . "\n";
//$testRealList->saveVarToFile("testData", 'RealList_new');



$testStandardMatrix = TIVarFile::loadFromFile('testData/Matrix_3x3_standard.8xm');
echo 'Before: ' . $testStandardMatrix->getReadableContent() . "\n   Now: ";
$testStandardMatrix->setContentFromString('[[1,2,3][4,5,6][-7,-8,-9]]');
$testStandardMatrix->setContentFromString('[[1,2,3][4,5,6][-7,-8,-9][1,2,3][4,5,6][-7,-8,-9]]');
echo $testStandardMatrix->getReadableContent() . "\n";
//$testStandardMatrix->saveVarToFile('testData', 'Matrix_new');



$testComplex = TIVarFile::loadFromFile('testData/Complex.8xc'); // -5 + 2i
echo 'Before: ' . $testComplex->getReadableContent() . "\n   Now: ";
assert($testComplex->getReadableContent() === '-5+2i');
$newComplex = TIVarFile::createNew(TIVarType::createFromName('Complex'), 'C');
$newComplex->setContentFromString('-5+2i');
assert($testComplex->getRawContent() === $newComplex->getRawContent());
$newComplex->setContentFromString('2.5+0.001i');
echo $newComplex->getReadableContent() . "\n";
//$newComplex->saveVarToFile('testData', "Complex_new");



$testComplexList = TIVarFile::loadFromFile('testData/ComplexList.8xl');
echo 'Before: ' . $testComplexList->getReadableContent() . "\n   Now: ";
$testComplexList->setContentFromString('{9+2i, 0i, .5, -0.5+6e-8i}');
echo $testComplexList->getReadableContent() . "\n";
//$testComplexList->saveVarToFile('testData', 'ComplexList_new');



$testExact_RealRadical = TIVarFile::loadFromFile('testData/Exact_RealRadical.8xn');
echo 'Before: ' . $testExact_RealRadical->getReadableContent() . "\n";
assert($testExact_RealRadical->getReadableContent() === '(41*√(789)+14*√(654))/259');
$newExact_RealRadical = TIVarFile::createNew(TIVarType::createFromName('ExactRealRadical'), 'A', TIModel::createFromName('83PCE'));
//$newExact_RealRadical->setContentFromString('-42.1337');
//assert($testExact_RealRadical->getRawContent() === $newExact_RealRadical->getRawContent());
//$newExact_RealRadical->saveVarToFile('testData', "Exact_RealRadical_new");



$testExactComplexFrac = TIVarFile::loadFromFile('testData/Exact_ComplexFrac.8xc');
echo 'Before: ' . $testExactComplexFrac->getReadableContent() . "\n";
assert($testExactComplexFrac->getReadableContent() === '1/5-2/5i');
$newExactComplexFrac = TIVarFile::createNew(TIVarType::createFromName('ExactComplexFrac'), 'A', TIModel::createFromName('83PCE'));
//$newExactComplexFrac->setContentFromString('-42.1337');
//assert($testExactComplexFrac->getRawContent() === $newExactComplexFrac->getRawContent());
//$newExactComplexFrac->saveVarToFile("testData", "Exact_ComplexFrac_new");



$testExactComplexPi = TIVarFile::loadFromFile('testData/Exact_ComplexPi.8xc');
echo 'Before: ' . $testExactComplexPi->getReadableContent() . "\n";
assert($testExactComplexPi->getReadableContent() === '1/5-3*π*i');
$newExactComplexPi = TIVarFile::createNew(TIVarType::createFromName('ExactComplexPi'), 'A', TIModel::createFromName('83PCE'));
//$newExactComplexPi->setContentFromString('-42.1337');
//assert($testExactComplexPi->getRawContent() === $newExactComplexPi->getRawContent());
//$newExactComplexPi->saveVarToFile("testData", "Exact_ComplexPi_new");



$testExactComplexPiFrac = TIVarFile::loadFromFile('testData/Exact_ComplexPiFrac.8xc');
echo 'Before: ' . $testExactComplexPiFrac->getReadableContent() . "\n";
assert($testExactComplexPiFrac->getReadableContent() === '2/7*π*i');
$newExactComplexPiFrac = TIVarFile::createNew(TIVarType::createFromName('ExactComplexPiFrac'), 'A', TIModel::createFromName('83PCE'));
//$newExactComplexPiFrac->setContentFromString('-42.1337');
//assert($testExactComplexPiFrac->getRawContent() === $newExactComplexPiFrac->getRawContent());
//$newExactComplexPiFrac->saveVarToFile("testData", "Exact_ComplexPiFrac_new");



$testExactComplexRadical = TIVarFile::loadFromFile('testData/Exact_ComplexRadical.8xc');
assert($testExactComplexRadical->getReadableContent() === '((√(6)+√(2))/4)+((√(6)-√(2))/4)*i');
echo 'Before: ' . $testExactComplexRadical->getReadableContent() . "\n";
$newExactComplexRadical = TIVarFile::createNew(TIVarType::createFromName('ExactComplexRadical'), 'A', TIModel::createFromName('83PCE'));
//$newExactComplexRadical->setContentFromString('-42.1337');
//assert($testExactComplexRadical->getRawContent() === $newExactComplexRadical->getRawContent());
//$newExactComplexRadical->saveVarToFile("testData", "Exact_ComplexRadical_new");



$testExactRealPi = TIVarFile::loadFromFile('testData/Exact_RealPi.8xn');
assert($testExactRealPi->getReadableContent() === '30*π');
echo 'Before: ' . $testExactRealPi->getReadableContent() . "\n";
$newExactRealPi = TIVarFile::createNew(TIVarType::createFromName('ExactRealPi'), 'A', TIModel::createFromName('83PCE'));
//$newExactRealPi->setContentFromString('-42.1337');
//assert($testExactRealPi->getRawContent() === $newExactRealPi->getRawContent());
//$newExactRealPi->saveVarToFile("testData", "Exact_RealPi_new");



$testExactRealPiFrac = TIVarFile::loadFromFile('testData/Exact_RealPiFrac.8xn');
assert($testExactRealPiFrac->getReadableContent() === '2/7*π');
echo 'Before: ' . $testExactRealPiFrac->getReadableContent() . "\n";
$newExactRealPiFrac = TIVarFile::createNew(TIVarType::createFromName('ExactRealPiFrac'), 'A', TIModel::createFromName('83PCE'));
//$newExactRealPiFrac->setContentFromString('-42.1337');
//assert($testExactRealPiFrac->getRawContent() === $newExactRealPiFrac->getRawContent());
//$newExactRealPiFrac->saveVarToFile("testData", "Exact_RealPiFrac_new");



//$testMatrixStandard = TIVarFile::loadFromFile('testData/Matrix_3x3_standard.8xm');
//print_r($testMatrixStandard);
//echo "Before: " . $testExactRealFrac->getReadableContent() . "\t" . "Now: ";
//$testExactRealFrac->setContentFromString("0.2");
//echo $testExactRealFrac->getReadableContent() . "\n";
//$testExactRealFrac->saveVarToFile();
