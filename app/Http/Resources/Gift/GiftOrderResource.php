<?php

namespace App\Http\Resources\Gift;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftOrderResource extends JsonResource
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
            'key'=> $this->key,
            'userId'=> $this->user_id,
            'promoCodeId'=> $this->promo_code_id,
            'message'=> $this->message,
            'recipentName'=> $this->recipent_name,
            'currency'=> $this->currency,
            'totalAmount'=> $this->total_amount,
            'status'=> $this->status,
            'transactionDate'=> $this->transaction_date,
        ];
    }
}
