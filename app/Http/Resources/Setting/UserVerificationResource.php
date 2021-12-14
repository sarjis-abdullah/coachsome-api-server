<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class UserVerificationResource extends JsonResource
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
            'userId'=> $this->user_id,
            'emailVerifiedAt'=> $this->email_verified_at,
            'phoneNumberVerified_at'=> $this->phone_number_verified_at,
            'facebookConnected_at'=> $this->facebook_connected_at,
            'googleConnected_at'=> $this->google_connected_at,
            'twitterConnected_at'=> $this->twitter_connected_at,
            'createdAt'=> $this->created_at,
            'updatedAt'=> $this->updated_at,
        ];
    }
}
