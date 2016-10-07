<?php
/*
 * Part of tivars_lib
 * (C) 2015-2016 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */


// Adapted from http://stackoverflow.com/a/32903747/378298
function dec2frac($num = 0.0, $err = 0.001)
{
    if ($err <= 0.0 || $err >= 1.0)
    {
        $err = 0.001;
    }
    
    $sign = ( $num > 0 ) ? 1 : ( ( $num < 0 ) ? -1 : 0 );

    if ($sign === -1)
    {
        $num = abs($num);
    }

    if ($sign !== 0)
    {
        // $err is the maximum relative $err; convert to absolute
        $err *= $num;
    }

    $n = (int) floor($num);
    $num -= $n;

    if ($num < $err)
    {
        return (string)($sign * $n);
    }

    if (1 - $err < $num)
    {
        return (string)($sign * ($n + 1));
    }

    // The lower fraction is 0/1
    $lower_n = 0;
    $lower_d = 1;

    // The upper fraction is 1/1
    $upper_n = 1;
    $upper_d = 1;

    while (true)
    {
        // The middle fraction is ($lower_n + $upper_n) / (lower_d + $upper_d)
        $middle_n = $lower_n + $upper_n;
        $middle_d = $lower_d + $upper_d;

        if ($middle_d * ($num + $err) < $middle_n)
        {
            // real + $err < middle : middle is our new upper
            $upper_n = $middle_n;
            $upper_d = $middle_d;
        }
        else if ($middle_n < ($num - $err) * $middle_d)
        {
            // middle < real - $err : middle is our new lower
            $lower_n = $middle_n;
            $lower_d = $middle_d;
        }
        else
        {
            // Middle is our best fraction
            return (string)(($n * $middle_d + $middle_n) * $sign) . '/' . (string)$middle_d;
        }
    }
}