<?php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'id'=>$this->id,
            'customerName'=> $this->packageBuyerUser ?  $this->packageBuyerUser->first_name.' '.$this->packageBuyerUser->last_name : ''
        ];
    }
}
