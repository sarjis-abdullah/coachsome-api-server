<?php

namespace App\Http\Resources\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageDetailsResource extends JsonResource
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
            'packageId'=> $this->package_id,
            'title'=> $this->title,
            'description'=> $this->description,
            'session'=> $this->session,
            'timePerSession'=> $this->time_per_session,
            'attendeesMin'=> $this->attendees_min,
            'attendeesMax'=> $this->attendees_max,
            'completedDays'=> $this->completed_by_days,
            'price'=> $this->price,
            'isSpecialPrice'=> $this->is_special_price,
            'discount'=> $this->discount,
            'transportFee'=> $this->transport_fee,
        ];
    }
}
