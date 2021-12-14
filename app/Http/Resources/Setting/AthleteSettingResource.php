<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class AthleteSettingResource extends JsonResource
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
            'id' => $this->id,
            'userId' => $this->user_id,
            'inboxMessage' => $this->inbox_message,
            'orderMessage' => $this->order_message,
            'orderUpdate' => $this->order_update,
            'bookingRequest' => $this->booking_request,
            'bookingChange' => $this->booking_change,
            'account' => $this->account,
            'marketting' => $this->marketting,
        ];
    }
}
