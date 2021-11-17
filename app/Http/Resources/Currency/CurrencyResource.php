<?php

namespace App\Http\Resources\Currency;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'symbol' => $this->symbol,
            'decimalDigits' => $this->decimal_digits,
            'thousandSeparator' => $this->thousand_separator,
            'fractionSeparator' => $this->fraction_separator,
            'symbolSpacing' => $this->symbol_spacing,
            'exchnageRate' => $this->exchange_rate,
        ];
    }
}
