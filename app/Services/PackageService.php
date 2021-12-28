<?php


namespace App\Services;

use App\Data\TransactionType;
use App\Entities\Currency;
use App\Entities\GiftTransaction;
use App\Entities\Package;
use App\Entities\PackageUserSetting;
use App\Entities\PromoCode;
use App\Entities\User;
use App\Services\Promo\PromoService;
use Illuminate\Support\Facades\Auth;

class PackageService
{
    public function getMaxRange()
    {
        $maxRange = 100;
        $count = PackageUserSetting::count();
        if ($count > 0) {
            $maxRange = PackageUserSetting::max('hourly_rate');
        }
        return $maxRange;
    }

    public function getMinRange()
    {
        return 0;
    }

    public function calculatePackageSalePrice($originalPrice, $discount)
    {
        $salePrice = 0;
        if ($discount && $discount > 0) {
            $givenOriginalPrice = $originalPrice;
            $givenDiscount = $discount;
            $calculateDiscount = $givenOriginalPrice * $givenDiscount / 100;
            $salePrice = $givenOriginalPrice - $calculateDiscount;
        } else {
            $salePrice = $originalPrice;
        }
        return $salePrice;
    }

    /**
     * Calculate package price
     *
     * @param User $user
     * @param Package $package
     * @return float|int
     */
    public function calculateOriginalPrice(User $user, Package $package)
    {
        $price = 0.00;
        $details = $package->details;
        if ($details && $user) {
            // Special price has no dependency
            // Hourly rate price has no specific amount it is dependent on session, time, hourly rate
            if ($details->is_special_price) {
                $price = $details->price;
            } else {
                $userPackageSetting = $user->ownPackageSetting;
                $session = $details->session;
                $timePerSession = $details->time_per_session;
                $hourlyRate = $userPackageSetting->hourly_rate;
                if ($session || $timePerSession) {
                    $price = $session * ($hourlyRate * ($timePerSession / 60));
                }
            }
        }
        return $price;
    }


    /**
     * Get package charge information
     * This method only used before order a package
     * After order a package order table contains package charge info
     * Do not use it after order a package to get package charge
     *
     * @param object $package
     * @param string $toCurrencyCode
     * @param array $otherInfo
     *
     * @return array $data
     */
    public function chargeInformation($package, $toCurrencyCode, $otherInfo = [])
    {
        $data = [
            'originalPrice' => 0.00,
            'salePrice' => 0.00,
            'serviceFee' => 0.00,
            'totalPerPerson' => 0.00,
            'promoDiscount' => 0.00,
            'giftCard' => [
                'payableAmount' => 0.00,
                'balanceAfterPaid' => 0.00
            ],
            'total' => 0.00,
        ];

        if ($package) {
            $currencyService = new CurrencyService();
            $promoService = new PromoService();

            $packageOwnerUser = $package->user;
            $packageDiscount = $package->details->discount ?? 0.00;
            $originalPrice = $this->calculateOriginalPrice($packageOwnerUser, $package);
            $fromCurrencyCode = $currencyService->getUserCurrency($packageOwnerUser)->code;
            $salePrice = $this->calculatePackageSalePrice($originalPrice, $packageDiscount);
            $salePriceAfterConvertingCurrency = $currencyService->convert(
                $salePrice,
                $fromCurrencyCode,
                $toCurrencyCode
            );

            $serviceFee = round((5 / 100) * $salePriceAfterConvertingCurrency, 2);
            $total = round(($salePriceAfterConvertingCurrency + $serviceFee), 2);
            $totalPerPerson = round((1 * ($salePriceAfterConvertingCurrency + $serviceFee)), 2);

            $promoDiscount = 0.00;
            if (array_key_exists('promoCode', $otherInfo) && array_key_exists('packageBuyerUser', $otherInfo)) {
                $promoCode = PromoCode::where('code', $otherInfo['promoCode'])->first();
                if ($promoCode) {
                    if (!$promoService->isExpired($promoCode, $otherInfo['packageBuyerUser'])) {
                        $promoDiscount = $promoService->calculateDiscount($promoCode->code, $total, $toCurrencyCode);
                        $total = $total - $promoDiscount;
                        $totalPerPerson = $totalPerPerson - $promoDiscount;
                    }
                }
            }

            // Total amount will be changed if there gift amount added
            $giftCardBalance = 0.00;
            if ($otherInfo['useGiftCard']) {
                $giftCardTransactions = GiftTransaction::where('user_id', Auth::id())
                    ->get()
                    ->each(function ($item) use ($currencyService, $toCurrencyCode, &$giftCardBalance) {
                        $amount =  $currencyService->convert(
                            $item->amount,
                            $item->currency,
                            $toCurrencyCode
                        );
                        if ($item->type == TransactionType::DEBIT) {
                            $giftCardBalance += $amount;
                        } else {
                            $giftCardBalance -= $amount;
                        }
                    });

                // Gift card balance and total balance need to check amount so that calculate the blance easily
                if($giftCardBalance > 0){
                    $payableAmount = 0.00;
                    if ($total > $giftCardBalance) {
                        $total = $total - $giftCardBalance;
                        $payableAmount =  $giftCardBalance;
                        $giftCardBalance = 0.00;
                    } else {
                        $giftCardBalance = $giftCardBalance - $total;
                        $payableAmount =  $total;
                        $total = 0.00;
                    }              
                    $data['giftCard']['payableAmount'] = $payableAmount;
                    $data['giftCard']['balanceAfterPaid'] = $giftCardBalance;
                }
            }
            
            $data['originalPrice'] = $originalPrice;
            $data['salePrice'] = $salePrice;
            $data['serviceFee'] = $serviceFee;
            $data['totalPerPerson'] = $totalPerPerson;
            $data['promoDiscount'] = $promoDiscount;
            $data['total'] = $total;
        }


        return $data;
    }

    public static function calculateAmountByUserBasedCurrency($amount, $userBasedCurrency, $toConvertCurrency)
    {
        $calculateAmount = 0;
        $mCurrency = new Currency();
        $defaultBasedCurrency = $mCurrency->getDefaultBasedCurrency();
        if($defaultBasedCurrency->id == $userBasedCurrency->id){
             $calculateAmount = $amount * $toConvertCurrency->exchange_rate;
        } else {
            $calculateAmount = $amount / $toConvertCurrency->exchange_rate;
        }
        return $calculateAmount;
    }
}
