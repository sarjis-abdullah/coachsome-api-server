<?php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Resources\Json\JsonResource;

class PendingBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $customerUser = $this->customerUser;
        $packageOwnerUser = $this->packageOwnerUser;
        $packageTitle = $this->package ? $this->package->details->title ?? '' : '';
        return [
            'id' => $this->id,
            'name' => $customerUser->full_name,
            'email' => $customerUser->email,
            'mobileNumber' => $this->customer_mobile_no,
            'customerText' => $this->customer_text,
            'packageOwnerName' => $packageOwnerUser->first_name.' '.$packageOwnerUser->last_name,
            'packageTitle' => $packageTitle,
            'bookingStatus' => $this->booking_status,
            'mailSent' => date('F j, Y, g:i a', strtotime($this->booking_date)),
            'confirmedDate' =>  $this->confirm_mail_date
                ? date('F j, Y, g:i a', strtotime($this->confirm_mail_date))
                : '',
        ];
    }
}
