<?php

namespace App\Services\Promo;

use App\Data\Promo;
use App\Entites\PromoUser;
use App\Entities\Currency;
use App\Entities\PromoCode;
use App\Services\CurrencyService;

class PromoService
{
    public function calculateDiscount($code, $total,$toCurrencyCode)
    {
        $discount = 0.00;
        $currencyService = new CurrencyService();
        $promoCode = PromoCode::where('code', $code)->first();
          if($promoCode){
              if ($promoCode->promo_type_id == Promo::TYPE_ID_FIXED) {
                  $promoCodeCurrency = Currency::find($promoCode->currency_id);
                  $discount = $currencyService->convert(
                      $promoCode->discount_amount,
                      $promoCodeCurrency->code,
                      $toCurrencyCode
                  );
                  ;
              } else {
                  $discount = ($promoCode->percentage_off / 100) * $total;
              }
          }
          return $discount;
    }


    public function isExpired(PromoCode $promoCode, $user)
    {
        $isExpired = false;
        if($promoCode){
            if($promoCode->promo_type_id == Promo::TYPE_ID_FIXED){
                $promoUser = PromoUser::where('code', $promoCode->code)->first();
                if($promoUser){
                    $isExpired = true;
                }
            } else {
                $promoUser = PromoUser::where('code', $promoCode->code)
                    ->where('user_id', $user->id)
                    ->first();
                if($promoUser){
                    $isExpired = true;
                }
            }
        }

       return $isExpired;
    }
}
