<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars;

include_once "autoloader.php";

/* TODO: Use PHPUnit */

$expected = 32;
$actual   = TIVarTypes::getTypeIdFromString("ExactRealPi");
echo "$expected === " . 'TIVarTypes::getTypeIdFromString("ExactRealPi")' . " ?\t" . ($expected === $actual ? 'true' : 'false');

?>