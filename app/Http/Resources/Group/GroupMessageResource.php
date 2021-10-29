<?php

namespace App\Http\Resources\Group;

use App\Entities\User;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GroupMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $senderUser = User::find($this->sender_user_id);
        return [
            'id' => $this->id,
            'type' => $this->type,
            'me' => $this->sender_user_id == Auth::id(),
            'content' => json_decode($this->content),
            'createdAt' => $this->date_time_iso,
            'groupId' => $this->group_id,
            'categoryId' => $this->message_category_id,
            'senderUserId' => $this->sender_user_id,
            'senderUser' => $senderUser ? new UserResource($senderUser) : []
        ];
    }
}
