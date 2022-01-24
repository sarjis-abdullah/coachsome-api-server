<?php


namespace App\Utils;

use App\Entities\CurrencyRate;
use Carbon\Carbon;
use Exception;

/**
 * Currency uitl helps to take action on amount
 * Base currency is DKK
 * System contains only one currency for removing collision
 */
class CurrencyUtil
{
    /**
     * Convert currency according to exchange rates
     *
     * @param float $amount
     * @param string $from
     * @param string $to
     * @param string $date
     *
     * @return float $result
     */
    public static function convert($amount, $from, $to, $date = null)
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
            $currencyRate = CurrencyRate::where('base', $from)
                ->where("date", $historyDate)
                ->first();

            if (!$currencyRate) {
                $rates = self::rates($from, $historyDate);
                $currencyRate = new CurrencyRate();
                $currencyRate->base = $from;
                $currencyRate->date = $historyDate;
                $currencyRate->rates = json_encode($rates);
                $currencyRate->save();
            }
            $rates = json_decode($currencyRate->rates, true);
            $rate = $rates[$to];
        } else {
            $currencyRate = CurrencyRate::where('base', $from)
                ->where("date", $latestDate)
                ->first();
            if (!$currencyRate) {
                $rates = self::rates($from);
                $currencyRate = new CurrencyRate();
                $currencyRate->base = $from;
                $currencyRate->date = $latestDate;
                $currencyRate->rates = json_encode($rates);
                $currencyRate->save();
            }
            $rates = json_decode($currencyRate->rates, true);
            $rate = $rates[$to];
        }

        $result = $amount * $rate;

        return round($result,2);
    }

    /**
     * Get exchange rate according to base currency
     *
     * @param string $base
     * @param string $date
     *
     * @return array $data
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
