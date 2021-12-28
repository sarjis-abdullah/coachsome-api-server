<?php


namespace App\Utils;


class StringUtil
{
    public static function firstWord($sentence)
    {
        return explode(" ", $sentence)[0];
    }

    public static function lastWord($sentence)
    {
        $words = explode(" ", $sentence);
        $countWord = count($words);
        return explode(" ", $sentence)[$countWord - 1];
    }
}
