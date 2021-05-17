<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageSetting extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return  [
            'id' => $this->id,
            'userId' => $this->user_id,
            'hourlyRate' => $this->hourly_rate,
            'isQuickBooking' => $this->is_quick_booking,
        ];
    }
}
