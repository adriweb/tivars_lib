<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

include_once "autoloader.php";

use tivars\TIVarType;

/* TODO: Use PHPUnit */

/* Types */
$expected = 32;
$actual = TIVarType::getIDFromName("ExactRealPi");
echo "$expected === " . 'TIVarTypes::getTypeIdFromString("ExactRealPi")' . " ?\t" . ($expected === $actual ? 'true' : 'false') . "\n";


?>