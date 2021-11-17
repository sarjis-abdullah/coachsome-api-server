<?php

namespace App\Http\Resources\Promo;

use App\Entities\PromoUser;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=> $this->id,
            'name'=> $this->name,
            'code'=> $this->code,
            'discount'=> $this->discount_amount,
            'duration'=> $this->promo_duration_id,
            'percentageOff'=> $this->percentage_off,
            'currency'=> $this->currency_id,
            'totalUsed'=> PromoUser::where('code', $this->code)->count(),
            'type'=> $this->promo_type_id,
        ];
    }
}
