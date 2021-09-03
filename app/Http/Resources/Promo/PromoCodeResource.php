<?php

namespace App\Http\Resources\Promo;

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
            'duration'=> $this->duration_type_id,
            'totalUsed'=> null,
            'type'=> $this->promo_type_id,
        ];
    }
}
