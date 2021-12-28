<?php


namespace App\Utils;

use App\Data\CurrencyCode;
use App\Entities\CurrencyRate;
use Carbon\Carbon;
use Exception;

class CurrencyUtil
{
    /**
     * Convert currency according base currency Danish Kroner
     * 
     * @param $amount float
     * @param $code string
     * @param $date string
     * 
     * @return $result float
     */
    public static function convert($amount, $code, $date = null)
    {
        $rate = null;
        $isHistory = false;
        $latestDate = null;
        $historyDate = null;
        $result = null;

        if ($date) {
            $isHistory = true;
            $historyDate = Carbon::parse($date)->format("Y-m-d");
        } else {
            $latestDate = Carbon::now()->format("Y-m-d");
        }
        if ($isHistory) {
            $currencyRate = CurrencyRate::where('base', CurrencyCode::DANISH_KRONER)
                ->where("date", $historyDate)
                ->first();
            if (!$currencyRate) {
                $rates = self::rates(CurrencyCode::DANISH_KRONER, $historyDate);
                $currencyRate = new CurrencyRate();
                $currencyRate->base = CurrencyCode::DANISH_KRONER;
                $currencyRate->date = $historyDate;
                $currencyRate->rates = json_encode($rates);
                $currencyRate->save();
            }
            $rates = json_decode($currencyRate->rates, true);
            $rate = $rates[$code];
        } else {
            $currencyRate = CurrencyRate::where('base', CurrencyCode::DANISH_KRONER)
                ->where("date", $latestDate)
                ->first();
            if (!$currencyRate) {
                $rates = self::rates(CurrencyCode::DANISH_KRONER);
                $currencyRate = new CurrencyRate();
                $currencyRate->base = CurrencyCode::DANISH_KRONER;
                $currencyRate->date = $latestDate;
                $currencyRate->rates = json_encode($rates);
                $currencyRate->save();
            }
            $rates = json_decode($currencyRate->rates, true);
            $rate = $rates[$code];
        }

        $result = $amount * $rate;

        return $result;
    }

    /**
     * Get exchange rate according to base currency
     * 
     * @param $base string
     * @param $date string
     * 
     * @return $data array
     */
    public static function rates($base, $date = null)
    {
        $data = [];
        if ($date) {
            $req_url = 'https://api.exchangerate.host/' . $date . '?base=' . $base;
        } else {
            $req_url = 'https://api.exchangerate.host/latest?base=' . $base;
        }
        $response_json = file_get_contents($req_url);
        if (false !== $response_json) {
            try {
                $response = json_decode($response_json, true);
                if ($response['success'] === true) {
                    $data = $response["rates"];
                }
            } catch (Exception $e) {
            }
        }
        return $data;
    }
}
