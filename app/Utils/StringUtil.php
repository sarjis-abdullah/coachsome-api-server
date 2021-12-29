<?php


namespace App\Utils;

/**
 * String util helps to format any tirng
 */
class StringUtil
{
    /**
     * Get the first word of the string
     *
     * @param string $sentence
     * @return string
     */
    public static function firstWord($sentence)
    {
        return explode(" ", $sentence)[0];
    }

    /**
     * Get the last word of the string
     *
     * @param string $sentence
     * @return string
     */
    public static function lastWord($sentence)
    {
        $words = explode(" ", $sentence);
        $countWord = count($words);
        return explode(" ", $sentence)[$countWord - 1];
    }
}
