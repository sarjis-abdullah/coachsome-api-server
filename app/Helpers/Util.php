<?php


namespace App\Helpers;


use App\Entities\Currency;
use App\Entities\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Util
{
    public function getUrl($userName)
    {
        return env('APP_CLIENT_DOMAIN') . $userName;
    }

    public function getUserName($firstName, $lastName)
    {
        $username = strtolower(explode(" ", $firstName)[0]) . '.' . strtolower(explode(" ", $lastName)[0]);
        $userRows = User::whereRaw("user_name REGEXP '^{$username}(-[0-9]*)?$'")->get();
        $countUser = count($userRows) + 1;
        return ($countUser > 1) ? "{$username}-{$countUser}" : $username;
    }

    public static function calculateAmountByUserBasedCurrency($amount, $userBasedCurrency, $toConvertCurrency)
    {
        $calculateAmount = 0;
        $mCurrency = new Currency();
        $defaultBasedCurrency = $mCurrency->getDefaultBasedCurrency();
        Log::info($defaultBasedCurrency);
        if($defaultBasedCurrency->id == $userBasedCurrency->id){
             $calculateAmount = $amount * $toConvertCurrency->exchange_rate;
        } else {
            $calculateAmount = $amount / $toConvertCurrency->exchange_rate;
        }

        return $calculateAmount;
    }
}
