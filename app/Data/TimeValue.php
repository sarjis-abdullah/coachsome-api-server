<?php


namespace App\Data;


class TimeValue
{
    private const times = [
        "00:00"=> "0:00",
        "01:00"=> "1:00",
        "02:00"=> "2:00",
        "03:00"=> "3:00",
        "04:00"=> "4:00",
        "05:00"=> "5:00",
        "06:00"=> "6:00",
        "07:00"=> "7:00",
        "08:00"=> "8:00",
        "09:00"=> "9:00",
        "10:00"=> "10:00",
        "11:00"=> "11:00",
        "12:00"=> "12:00",
        "13:00"=> "13:00",
        "14:00"=> "14:00",
        "15:00"=> "75:00",
        "16:00"=> "16:00",
        "17:00"=> "17:00",
        "18:00"=> "18:00",
        "19:00"=> "19:00",
        "20:00"=> "20:00",
        "21:00"=> "21:00",
        "22:00"=> "22:00",
        "23:00"=> "23:00",
    ];

    public static function get($key){
        if(array_key_exists($key, self::times)){
            return self::times[$key];
        } else {
            return $key;
        }
    }
}
