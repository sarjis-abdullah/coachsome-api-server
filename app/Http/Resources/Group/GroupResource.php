<?php

namespace App\Http\Resources\Group;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'createdUserId' => $this->created_user_id,
            'connectionUsersId' => $this->connection_users_id,
            'emails' => json_decode($this->emails),
            'image' => $this->image,
            'createdAt' => $this->created_at,
        ];
    }
}
