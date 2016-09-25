<?php
/*
 * Part of tivars_lib
 * (C) 2015-2016 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

function gcd($a, $b)
{
    return ($a % $b) ? gcd($b, $a % $b) : $b;
}

function dec2frac($num = 0.0)
{
    $num = (float)$num;

    $neg = '';
    if ($num < 0.0)
    {
        $neg = '-';
        $num = abs($num);
    }
    $base = floor($num);
    $num -= $base;
    if ($num == 0)
    {
        return $neg . $base;
    }

    list(, $numerator) = preg_split('/\./', $num, 2);
    $denominator = pow(10, strlen($numerator));
    $gcd = gcd($numerator, $denominator);

    $norm_numer = ($numerator / $gcd);
    $norm_denom = ($denominator / $gcd);

    return ($base > 0) ? ($neg . ($base * $norm_denom + $norm_numer). '/' . $norm_denom)
                       : ($neg . ($norm_numer . '/' . $norm_denom));
}
