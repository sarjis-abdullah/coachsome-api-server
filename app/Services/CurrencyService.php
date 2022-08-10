<?php


namespace App\Services;


use App\Entities\Currency;
use App\Entities\CurrencyRate;
use App\Utils\CurrencyUtil;
use Carbon\Carbon;
use Exception;

use Illuminate\Support\Facades\Log;
use NumberFormatter;

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
        return CurrencyUtil::convert($amount, $fromCurrencyCode, $toCurrencyCode, $date = null);
    }

    public function rates($base, $date = null)
    {
        return CurrencyUtil::rates($base, $date);
    }

    public function format($amount, $code)
    {
        if ($code == 'SEK') {
            $formatter = new \NumberFormatter('sv_SE', \NumberFormatter::CURRENCY);
            $formatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '.');
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
