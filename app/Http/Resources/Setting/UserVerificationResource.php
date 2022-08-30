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
            'phoneNumberVerifiedAt'=> $this->phone_number_verified_at,
            'facebookConnectedAt'=> $this->facebook_connected_at,
            'googleConnectedAt'=> $this->google_connected_at,
            'twitterConnectedAt'=> $this->twitter_connected_at,
            'appleConnectedAt'=> $this->apple_connected_at,
            'createdAt'=> $this->created_at,
            'updatedAt'=> $this->updated_at,
        ];
    }
}
