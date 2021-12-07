<?php


namespace App\Services;


use App\Entities\Currency;
use App\Entities\CurrencyRate;
use Carbon\Carbon;

class CurrencyService
{
    /**
     * Convert currency
     *
     * @param float $amount
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @param string $date
     * @return float|null $result
     */
    public function convert($amount, $fromCurrencyCode, $toCurrencyCode, $date = null)
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
            $currencyRate = CurrencyRate::where('base', $fromCurrencyCode)
                ->where("date", $historyDate)
                ->first();
            if (!$currencyRate) {
                $rates = $this->rates($fromCurrencyCode, $historyDate);
                $currencyRate = new CurrencyRate();
                $currencyRate->base = $fromCurrencyCode;
                $currencyRate->date = $historyDate;
                $currencyRate->rates = json_encode($rates);
                $currencyRate->save();
            }
            $rates = json_decode($currencyRate->rates, true);
            $rate = $rates[$toCurrencyCode];
        } else {
            $currencyRate = CurrencyRate::where('base', $fromCurrencyCode)
                ->where("date", $latestDate)
                ->first();
            if (!$currencyRate) {
                $rates = $this->rates($fromCurrencyCode);
                $currencyRate = new CurrencyRate();
                $currencyRate->base = $fromCurrencyCode;
                $currencyRate->date = $latestDate;
                $currencyRate->rates = json_encode($rates);
                $currencyRate->save();
            }
            $rates = json_decode($currencyRate->rates, true);
            $rate = $rates[$toCurrencyCode];
        }

        $result = $amount * $rate;

        return $result;
    }

    public function rates($base, $date = null)
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

    public function format($amount, $code)
    {
        if ($code == 'SEK') {
            $formatter = new \NumberFormatter('sv_SE', \NumberFormatter::CURRENCY);
            $formatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '.');
            $formatter->setPattern('#,##0.## kr');
        } elseif ($code == 'EUR') {
            $formatter = new \NumberFormatter('en_GB', \NumberFormatter::CURRENCY);
        } else {
            $formatter = new \NumberFormatter('da_DK', \NumberFormatter::CURRENCY);
        }

        return $formatter->formatCurrency($amount, 'DKK');
    }

    /**
     * Get user currency
     * @param User $user
     * @return Currency $currency
     */
    public function getUserCurrency($user)
    {
        $currency = null;
        $mCurrency = new Currency();
        // There has no user currency, it will be changed in future 'insha Allah'
        $currency = $mCurrency->getDefaultBasedCurrency();
        return $currency;
    }

    public function getUserCurrencyByRequestHeader($request)
    {
        $currency = null;
        $mCurrency = new Currency();
        $requestedCurrencyCode = $request->header('Currency-Code');
        $requestedCurrency = $mCurrency->getByCode($requestedCurrencyCode);
        if ($requestedCurrency) {
            $currency = $requestedCurrency;
        } else {
            $currency = $mCurrency->getDefaultBasedCurrency();
        }
        return $currency;
    }

    public function getDefaultBasedCurrency()
    {
        return Currency::where('is_def_based_currency', 1)->first();
    }

    public function getByCode($code)
    {
        return Currency::where('code', $code)->first();
    }
}
