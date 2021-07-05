<?php


namespace App\Core\Rapi\Services;


class Util
{

    public static function array_has_dupes($array) {
        return count($array) !== count(array_unique($array));
    }

    /**
     * @param $number
     * @return string
     */
    public static function round($number){
        return number_format((float)$number, 2, '.', '');
    }
}
