<?php


namespace App\Services;


use App\Entities\Package;
use App\Entities\PackageUserSetting;
use App\Entities\User;
use Illuminate\Support\Facades\Log;

class PackageService
{
    public function getMaxRange()
    {
        $maxRange = 100;
        $count = PackageUserSetting::count();
        if($count > 0) {
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
        if($discount && $discount > 0){
            $givenOriginalPrice = $originalPrice;
            $givenDiscount = $discount;
            $calculateDiscount = $givenOriginalPrice* $givenDiscount/100;
            $salePrice= $givenOriginalPrice - $calculateDiscount;
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
}
