<?php

namespace App\Services\Promo;

use App\Data\Promo;
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
}
