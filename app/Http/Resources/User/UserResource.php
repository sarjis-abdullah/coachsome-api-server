<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Category\SportCategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'userName' => $this->user_name,
            'sportCategories'=> SportCategoryResource::collection($this->sportCategories),
            'profile'=> new ProfileResource($this->profile)
        ];
    }
}
